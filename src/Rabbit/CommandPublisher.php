<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Command\Properties\CommandProperties;
use App\Config\CommandConfig;
use App\Config\Config;
use App\Config\Connection as ConnectionConfig;
use App\Config\ExchangePublishedCommandConfig;
use App\Config\QueuePublishedCommandConfig;
use App\Serializer\Serializer;

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
        private MessageTransformerInterface $transformer
    ) {
    }

    public function publish(CommandInterface $command, ?CommandProperties $commandProperties = null): void
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
        if ($publisherConfig instanceof QueuePublishedCommandConfig) {
            $this->getConnection($publisherConfig->getConnection())->declareQueue($publisherConfig->getQueue());
        } elseif ($publisherConfig instanceof ExchangePublishedCommandConfig) {
            $connection = $this->getConnection($publisherConfig->getConnection());
            $connection->declareExchange($publisherConfig->getExchange());
            foreach ($publisherConfig->getExchange()->getQueueBindings() as $queueBinding) {
                $connection->declareQueue($queueBinding->getQueue());
                $connection->bindQueue($publisherConfig->getExchange(), $queueBinding);
            }
        }

        $this->declaredCommands[$commandConfig->getCommandClass()] = true;
    }
}
