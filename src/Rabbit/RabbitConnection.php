<?php

namespace App\Rabbit;

use App\Config\Binding;
use App\Config\Connection;
use App\Config\Exchange;
use App\Config\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitConnection implements ConnectionInterface
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    public function __construct(Connection $connectionConfig)
    {
        $this->connection = new AMQPStreamConnection(
            $connectionConfig->getHost(),
            $connectionConfig->getPort(),
            $connectionConfig->getUser(),
            $connectionConfig->getPassword(),
            $connectionConfig->getVHost()
        );
        $this->channel = $this->connection->channel();
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function declareQueue(Queue $queue): void
    {
        $this->channel->queue_declare(
            $queue->getName(),
            $queue->isPassive(),
            $queue->isDurable(),
            $queue->isExclusive(),
            $queue->isAutoDelete()
        );
    }

    public function declareExchange(Exchange $exchange): void
    {
        $this->channel->exchange_declare($exchange->getName(), $exchange->getType()->value);
    }

    public function bindQueue(Binding $binding): void
    {
        $this->channel->queue_bind(
            $binding->getQueue()->getName(),
            $binding->getExchange()->getName(),
            $binding->getRoutingKey()
        );
    }

    public function publish(AMQPMessage $msg, string $routingKey, string $exchange = ''): void
    {
        $this->channel->basic_publish($msg, $exchange, $routingKey);
    }

    public function consume(ConsumerParameters $parameters, ConsumerCallbackInterface $callback): void
    {
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
