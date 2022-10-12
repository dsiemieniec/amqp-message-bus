<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Config\Binding;
use App\Config\Connection;
use App\Config\ConsumerParameters;
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
    private bool $consumerStopped = false;

    public function __construct(
        private Connection $connectionConfig
    ) {
        $this->connection = new AMQPStreamConnection(
            $this->connectionConfig->getHost(),
            $this->connectionConfig->getPort(),
            $this->connectionConfig->getUser(),
            $this->connectionConfig->getPassword(),
            $this->connectionConfig->getVHost()
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
        $arguments = [];
        foreach ($queue->getArguments() as $argument) {
            $arguments[$argument->getKey()] = $argument->getValue();
        }
        $arguments = \count($arguments) > 0 ? new AMQPTable($arguments) : [];

        $this->channel->queue_declare(
            $queue->getName(),
            $queue->isPassive(),
            $queue->isDurable(),
            $queue->isExclusive(),
            $queue->isAutoDelete(),
            false,
            $arguments
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

    public function nack(AMQPMessage $msg, bool $requeue): void
    {
        $this->channel->basic_nack($msg->getDeliveryTag(), requeue: $requeue);
    }

    public function consume(Queue $queue, ConsumerCallbackInterface $callback): void
    {
        $this->consumerStopped = false;
        $parameters = $queue->getConsumerParameters();

        $this->channel->basic_qos(0, 1, false);
        $this->channel->basic_consume(
            $queue->getName(),
            $parameters->getTag(),
            !$parameters->isLocal(),
            !$parameters->isAck(),
            $parameters->isExclusive(),
            false,
            fn(AMQPMessage $message) => $callback->onMessage($message, $this)
        );

        $startedAt = \time();
        $i = 0;
        while ($this->channel->is_open() && !$this->consumerStopped) {
            $timeout = $this->calculateTimeout($startedAt, $parameters);
            if ($timeout > 0 && $timeout <= 1) {
                $this->stopConsumer();
            }
            $this->channel->wait(null, false, $timeout);
            if ($parameters->hasMessagesLimit() && ++$i >= $parameters->getMessagesLimit()) {
                $this->stopConsumer();
                throw new MessageLimitException($parameters->getMessagesLimit());
            }
            if ($this->timeLimitExceeded($startedAt, $parameters)) {
                $this->stopConsumer();
                throw new TimeLimitException($parameters->getTimeLimit());
            }
        }
    }

    public function stopConsumer(): void
    {
        $this->channel->stopConsume();
        $this->consumerStopped = true;
    }

    private function calculateTimeout(int $startedAt, ConsumerParameters $parameters): int
    {
        $timeout = $parameters->getWaitTimeout();
        if ($parameters->hasTimeLimit()) {
            $finishAt = $startedAt + $parameters->getTimeLimit();
            $finishTimeout = $finishAt - \time();
            if ($finishTimeout <= 1) {
                return 1;
            } elseif ($timeout > 0 && $finishTimeout < $timeout) {
                return $finishTimeout;
            }
        }

        return $timeout;
    }

    private function timeLimitExceeded(int $startedAt, ConsumerParameters $parameters): bool
    {
        return $parameters->hasTimeLimit() && \time() >= $startedAt + $parameters->getTimeLimit();
    }
}
