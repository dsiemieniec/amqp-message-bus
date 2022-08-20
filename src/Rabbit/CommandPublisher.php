<?php

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Serializer\CommandSerializerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CommandPublisher implements CommandPublisherInterface
{
    private const QUEUE_NAME = 'command_bus';
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;
    private bool $queueDeclared = false;

    public function __construct(
        private CommandSerializerInterface $serializer
    ) {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function publish(CommandInterface $command): void
    {
        $this->declareQueue();

        $msg = new AMQPMessage($this->serializer->serialize($command));
        $this->channel->basic_publish($msg, '', self::QUEUE_NAME);
    }

    private function declareQueue(): void
    {
        if (!$this->queueDeclared) {
            $this->channel->queue_declare(self::QUEUE_NAME, false, false, false, false);
            $this->queueDeclared = true;
        }
    }
}