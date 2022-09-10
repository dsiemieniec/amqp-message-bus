<?php

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Config\Config;
use App\Serializer\CommandSerializerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CommandPublisher implements CommandPublisherInterface
{
    public function __construct(
        private Config $config,
        private CommandSerializerInterface $serializer
    ) {
    }

    public function publish(CommandInterface $command): void
    {
        $publisherConfig = $this->config->getCommandPublisherConfig(\get_class($command));
        $connection = new RabbitConnection($publisherConfig->getConnection());

        $connection->publish(
            new AMQPMessage(
                $this->serializer->serialize($command)
            ),
            $publisherConfig->getPublisherTarget()
        );
    }
}
