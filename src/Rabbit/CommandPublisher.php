<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Command\Properties\CommandProperties;
use App\Config\Config;
use App\Config\Connection as ConnectionConfig;
use App\Serializer\Serializer;

class CommandPublisher implements CommandPublisherInterface
{
    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

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
        $publisherConfig = $this->config->getCommandConfig(\get_class($command))->getPublisherConfig();
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
}
