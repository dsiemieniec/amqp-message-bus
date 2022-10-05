<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Config\Config;
use App\Config\Connection as ConnectionConfig;
use App\Rabbit\Message\MessageEnvelopeInterface;
use App\Rabbit\Message\PropertyKey;
use App\Serializer\Serializer;
use App\Utils\Delay;
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

    public function publish(CommandInterface $command, ?Delay $delay = null): void
    {
        $message = $this->transformEnvelope(
            $this->serializer->serialize($command),
            $delay
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

    private function transformEnvelope(MessageEnvelopeInterface $envelope, ?Delay $delay = null): AMQPMessage
    {
        $properties = [];
        $headers =  [];
        foreach ($envelope->getProperties()->all() as $property) {
            if ($property->getKey()->equals(PropertyKey::Headers)) {
                $headers[] = [$property->getKey()->value => $property->getPropertyValueAsString()];
            } else {
                $properties[$property->getKey()->value] = $property->getPropertyValueAsString();
            }
        }

        $properties[PropertyKey::Type->value] = $envelope->getCommandClass();

        if ($delay !== null) {
            $headers[] = ['x-delay' => $delay->getValue()];
        }

        $properties['application_headers'] = new AMQPTable($headers);

        return new AMQPMessage((string) $envelope->getBody(), $properties);
    }
}
