<?php

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Config\Queue;
use App\Serializer\CommandSerializerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CommandPublisher implements CommandPublisherInterface
{
    private const QUEUE_NAME = 'command_bus';

    public function __construct(
    ) {
    }

    public function publish(CommandInterface $command): void
    {
//        $this->declareQueue();
//        $this->connection->publish(
//            new AMQPMessage(
//                $this->serializer->serialize($command)
//            ),
//            self::QUEUE_NAME
//        );
    }

    private function declareQueue(): void
    {
        //$this->connection->declareQueue(new Queue(self::QUEUE_NAME));
    }
}
