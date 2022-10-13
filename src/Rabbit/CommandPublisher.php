<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Command\Properties\CommandProperties;
use App\Config\Config;
use App\Config\Connection as ConnectionConfig;
use App\Command\Properties\PropertyKey;
use App\Serializer\Serializer;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class CommandPublisher implements CommandPublisherInterface
{
    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

    public function __construct(
        private Config $config,
        private Serializer $serializer
    ) {
    }

    public function publish(CommandInterface $command, ?CommandProperties $commandProperties = null): void
    {
        $message = $this->transformEnvelope(
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

    private function transformEnvelope(MessageEnvelopeInterface $envelope): AMQPMessage
    {
        $properties = [];
        $headers = [];
        foreach ($envelope->getProperties()->getHeaders()->all() as $header) {
            $headers[$header->getName()] = $header->getValue();
        }
        $properties['application_headers'] = new AMQPTable($headers);
        $properties[PropertyKey::Type->value] = $envelope->getCommandClass();

        return new AMQPMessage((string) $envelope->getBody(), $properties);
    }
}
