<?php

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Config\Config;
use App\Serializer\CommandSerializerInterface;
use App\Utils\Delay;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class CommandPublisher implements CommandPublisherInterface
{
    public function __construct(
        private Config $config,
        private CommandSerializerInterface $serializer
    ) {
    }

    public function publish(CommandInterface $command, ?Delay $delay = null): void
    {
        $publisherConfig = $this->config->getCommandPublisherConfig(\get_class($command));
        $connection = new RabbitConnection($publisherConfig->getConnection());

        $message = new AMQPMessage($this->serializer->serialize($command));
        if ($delay !== null) {
            $message->set('application_headers', new AMQPTable(['x-delay' => $delay->getValue()]));
        }
        $connection->publish(
            $message,
            $publisherConfig->getPublisherTarget()
        );
    }
}
