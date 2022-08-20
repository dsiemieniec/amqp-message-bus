<?php

namespace App\Rabbit;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitConnection implements ConnectionInterface
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;
    private array $declaredQueues = [];

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function declareQueue(Queue $queue): void
    {
        if (!\array_key_exists($queue->getName(), $this->declaredQueues)) {
            $this->channel->queue_declare(
                $queue->getName(),
                $queue->isPassive(),
                $queue->isDurable(),
                $queue->isExclusive(),
                $queue->isAutoDelete()
            );
            $this->declaredQueues[$queue->getName()] = true;
        }
    }

    public function publish(AMQPMessage $msg, string $routingKey, string $exchange = ''): void
    {
        $this->channel->basic_publish($msg, $exchange, $routingKey);
    }

    public function consume(ConsumerParameters $parameters, ConsumerCallbackInterface $callback): void
    {
        $this->declareQueue($parameters->getQueue());
        $this->channel->basic_consume(
            $parameters->getQueue()->getName(),
            $parameters->getTag(),
            !$parameters->isLocal(),
            !$parameters->isAck(),
            $parameters->isExclusive(),
            false,
            fn(AMQPMessage $message) => $callback->onMessage($message, $this)
        );

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    public function ack(AMQPMessage $msg): void
    {
        $this->channel->basic_ack($msg->getDeliveryTag());
    }

    public function nack(AMQPMessage $msg): void
    {
        $this->channel->basic_nack($msg->getDeliveryTag());
    }
}
