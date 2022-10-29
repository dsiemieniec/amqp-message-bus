<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;
use Siemieniec\AmqpMessageBus\Config\CommandConfig;
use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Config\Connection as ConnectionConfig;
use Siemieniec\AmqpMessageBus\Config\ExchangePublishedCommandConfig;
use Siemieniec\AmqpMessageBus\Config\QueuePublishedCommandConfig;
use Siemieniec\AmqpMessageBus\Serializer\Serializer;
use Psr\Log\LoggerInterface;

class CommandPublisher implements CommandPublisherInterface
{
    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

    /**
     * @var array<string, bool>
     */
    private array $declaredCommands = [];

    public function __construct(
        private Config $config,
        private Serializer $serializer,
        private MessageTransformerInterface $transformer,
        private LoggerInterface $logger
    ) {
    }

    public function publish(object $command, ?CommandProperties $commandProperties = null): void
    {
        $message = $this->transformer->transformEnvelope(
            $this->serializer->serialize($command, $commandProperties),
        );
        $commandConfig = $this->config->getCommandConfig(\get_class($command));
        $this->declareTargets($commandConfig);

        $publisherConfig = $commandConfig->getPublisherConfig();

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

    private function declareTargets(CommandConfig $commandConfig): void
    {
        if ($this->declaredCommands[$commandConfig->getCommandClass()] ?? false) {
            return;
        }

        $publisherConfig = $commandConfig->getPublisherConfig();
        if ($publisherConfig instanceof QueuePublishedCommandConfig && $publisherConfig->getQueue()->canAutoDeclare()) {
            $this->declareQueue($publisherConfig);
        } elseif (
            $publisherConfig instanceof ExchangePublishedCommandConfig
            && $publisherConfig->getExchange()->canAutoDeclare()
        ) {
            $this->declareExchange($publisherConfig);
        }

        $this->declaredCommands[$commandConfig->getCommandClass()] = true;
    }

    private function declareQueue(QueuePublishedCommandConfig $publisherConfig): void
    {
        $this->getConnection($publisherConfig->getConnection())->declareQueue($publisherConfig->getQueue());
    }

    private function declareExchange(ExchangePublishedCommandConfig $publisherConfig): void
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
