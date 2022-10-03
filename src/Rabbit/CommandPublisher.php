<?php

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Config\Config;
use App\Config\Connection as ConnectionConfig;
use App\Serializer\CommandSerializerInterface;
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
        private CommandSerializerInterface $serializer
    ) {
    }

    public function publish(CommandInterface $command, ?Delay $delay = null): void
    {
        $message = new AMQPMessage($this->serializer->serialize($command));
        if ($delay !== null) {
            $message->set('application_headers', new AMQPTable(['x-delay' => $delay->getValue()]));
        }
        $publisherConfig = $this->config->getCommandPublisherConfig(\get_class($command));
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
