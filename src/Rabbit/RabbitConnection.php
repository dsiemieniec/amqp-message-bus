<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Config\Binding;
use App\Config\Connection;
use App\Config\Exchange;
use App\Config\PublisherTarget;
use App\Config\Queue;
use App\Exception\MessageLimitException;
use App\Exception\TimeLimitException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

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
        $arguments = [];
        $type = $exchange->getType()->value;
        if ($exchange->isDelayedActive()) {
            $arguments = new AMQPTable(['x-delayed-type' => $type]);
            $type = 'x-delayed-message';
        }
        $this->channel->exchange_declare(
            $exchange->getName(),
            $type,
            auto_delete: false,
            arguments: $arguments
        );
    }

    public function bindQueue(Binding $binding): void
    {
        $this->channel->queue_bind(
            $binding->getQueue()->getName(),
            $binding->getExchange()->getName(),
            $binding->getRoutingKey()
        );
    }

    public function publish(AMQPMessage $msg, PublisherTarget $target): void
    {
        $this->channel->basic_publish($msg, $target->getExchange(), $target->getRoutingKey());
    }

    public function ack(AMQPMessage $msg): void
    {
        $this->channel->basic_ack($msg->getDeliveryTag());
    }

    public function nack(AMQPMessage $msg): void
    {
        $this->channel->basic_nack($msg->getDeliveryTag());
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


        $limits = $parameters->getLimits();
        $startedAt = time();
        $i = 0;
        while ($this->channel->is_open()) {
            $timeout = $this->calculateTimeout($startedAt, $limits);
            if ($timeout <= 1) {
                $this->channel->stopConsume();
            }
            $this->channel->wait(null, false, $timeout);
            if ($limits->hasMessagesLimit() && ++$i >= $limits->getMessagesLimit()) {
                $this->channel->stopConsume();
                throw new MessageLimitException($limits->getMessagesLimit());
            }
            if ($this->timeLimitExceeded($startedAt, $limits)) {
                throw new TimeLimitException($limits->getTimeLimit());
            }
        }
    }

    private function calculateTimeout(int $startedAt, ConsumerLimits $limits): int
    {
        $timeout = $limits->getTimeout();
        if ($limits->hasTimeLimit()) {
            $finishAt = $startedAt + $limits->getTimeLimit();
            $finishTimeout = $finishAt - \time();
            if ($finishTimeout <= 1) {
                return 1;
            } elseif ($timeout > 0 && $finishTimeout < $timeout) {
                return $finishTimeout;
            }
        }

        return $timeout;
    }

    private function timeLimitExceeded(int $startedAt, ConsumerLimits $limits): bool
    {
        return $limits->hasTimeLimit() && \time() >= $startedAt + $limits->getTimeLimit();
    }
}
