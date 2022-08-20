<?php

namespace App\Rabbit;

use App\Serializer\CommandSerializerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CommandConsumer implements CommandConsumerInterface
{
    private const QUEUE_NAME = 'command_bus';
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

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

    public function consume(): void
    {
        $this->declareQueue();
        $this->channel->basic_consume(
            self::QUEUE_NAME,
            '',
            false,
            false,
            false,
            false,
            fn(AMQPMessage $message) => $this->onMessage($message)
        );

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    private function declareQueue(): void
    {
        $this->channel->queue_declare(self::QUEUE_NAME, false, false, false, false);
    }

    private function onMessage(AMQPMessage $message): void
    {
        echo $message->getBody() . PHP_EOL;

        $this->channel->basic_ack($message->getDeliveryTag());
    }
}
