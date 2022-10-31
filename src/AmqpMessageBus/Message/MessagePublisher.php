<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Config\MessageConfig;
use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Config\Connection as ConnectionConfig;
use Siemieniec\AmqpMessageBus\Config\ExchangePublishedMessageConfig;
use Siemieniec\AmqpMessageBus\Config\QueuePublishedMessageConfig;
use Siemieniec\AmqpMessageBus\Rabbit\ConnectionInterface;
use Siemieniec\AmqpMessageBus\Rabbit\MessageTransformerInterface;
use Siemieniec\AmqpMessageBus\Rabbit\RabbitConnection;
use Siemieniec\AmqpMessageBus\Serializer\Serializer;
use Psr\Log\LoggerInterface;

final class MessagePublisher implements MessagePublisherInterface
{
    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

    /**
     * @var array<string, bool>
     */
    private array $declaredMessages = [];

    public function __construct(
        private Config $config,
        private Serializer $serializer,
        private MessageTransformerInterface $transformer,
        private LoggerInterface $logger
    ) {
    }

    public function publish(object $message, ?MessageProperties $messageProperties = null): void
    {
        $messageConfig = $this->config->getMessageConfig(\get_class($message));
        $message = $this->transformer->transformEnvelope(
            $this->serializer->serialize($message, $messageProperties),
        );

        $this->declareTargets($messageConfig);

        $publisherConfig = $messageConfig->getPublisherConfig();

        $this->getConnection($publisherConfig->getConnection())->publish(
            $message,
            $publisherConfig->getPublisherTarget()
        );
    }

    private function getConnection(ConnectionConfig $connectionConfig): ConnectionInterface
    {
        if (!\array_key_exists($connectionConfig->getName(), $this->connections)) {
            $this->connections[$connectionConfig->getName()] = new RabbitConnection($connectionConfig);
        }

        return $this->connections[$connectionConfig->getName()];
    }

    private function declareTargets(MessageConfig $messageConfig): void
    {
        if ($this->declaredMessages[$messageConfig->getMessageClass()] ?? false) {
            return;
        }

        $publisherConfig = $messageConfig->getPublisherConfig();
        if ($publisherConfig instanceof QueuePublishedMessageConfig && $publisherConfig->getQueue()->canAutoDeclare()) {
            $this->declareQueue($publisherConfig);
        } elseif (
            $publisherConfig instanceof ExchangePublishedMessageConfig
            && $publisherConfig->getExchange()->canAutoDeclare()
        ) {
            $this->declareExchange($publisherConfig);
        }

        $this->declaredMessages[$messageConfig->getMessageClass()] = true;
    }

    private function declareQueue(QueuePublishedMessageConfig $publisherConfig): void
    {
        $this->getConnection($publisherConfig->getConnection())->declareQueue($publisherConfig->getQueue());
    }

    private function declareExchange(ExchangePublishedMessageConfig $publisherConfig): void
    {
        $connection = $this->getConnection($publisherConfig->getConnection());
        $connection->declareExchange($publisherConfig->getExchange());
        foreach ($publisherConfig->getExchange()->getQueueBindings() as $queueBinding) {
            if (!$queueBinding->getQueue()->canAutoDeclare()) {
                $logMessage = \sprintf(
                    'Cannot declare binding to %s (routing_key: %s) because queue has disabled auto_declaration',
                    $queueBinding->getQueue()->getName(),
                    $queueBinding->getRoutingKey()
                );
                $this->logger->warning($logMessage);

                continue;
            }

            $connection->declareQueue($queueBinding->getQueue());
            $connection->bindQueue($publisherConfig->getExchange(), $queueBinding);
        }
    }
}
