<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Config\Connection;
use Siemieniec\AmqpMessageBus\Config\ConsumerParameters;
use Siemieniec\AmqpMessageBus\Config\Exchange;
use Siemieniec\AmqpMessageBus\Config\PublisherTarget;
use Siemieniec\AmqpMessageBus\Config\Queue;
use Siemieniec\AmqpMessageBus\Config\QueueBinding;
use Siemieniec\AmqpMessageBus\Exception\MessageLimitException;
use Siemieniec\AmqpMessageBus\Exception\TimeLimitException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Siemieniec\AmqpMessageBus\Rabbit\ConsumerCallbackInterface as RabbitConsumerCallbackInterface;

class RabbitConnection implements ConnectionInterface
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;
    private bool $consumerStopped = false;

    public function __construct(Connection $connectionConfig)
    {
        $this->connection = new AMQPStreamConnection(
            host: $connectionConfig->getHost(),
            port: $connectionConfig->getPort(),
            user: $connectionConfig->getUser(),
            password: $connectionConfig->getPassword(),
            vhost: $connectionConfig->getVHost(),
            insist: $connectionConfig->isInsist(),
            login_method: $connectionConfig->getLoginMethod(),
            locale: $connectionConfig->getLocale(),
            connection_timeout: $connectionConfig->getConnectionTimeout(),
            read_write_timeout: $connectionConfig->getReadWriteTimeout(),
            keepalive: $connectionConfig->isKeepAlive(),
            heartbeat: $connectionConfig->getHeartbeat(),
            ssl_protocol: $connectionConfig->getSslProtocol()
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
            $queue->isAutoDelete(),
            false,
            $queue->hasArguments() ? new AMQPTable($queue->getArguments()) : []
        );
    }

    public function declareExchange(Exchange $exchange): void
    {
        $this->channel->exchange_declare(
            exchange: $exchange->getName(),
            type: $exchange->getType(),
            passive: $exchange->isPassive(),
            durable: $exchange->isDurable(),
            auto_delete: $exchange->isAutoDelete(),
            internal: $exchange->isInternal(),
            arguments: $exchange->hasArguments() ? new AMQPTable($exchange->getArguments()) : []
        );
    }

    public function bindQueue(Exchange $exchange, QueueBinding $queueBinding): void
    {
        $this->channel->queue_bind(
            $queueBinding->getQueue()->getName(),
            $exchange->getName(),
            $queueBinding->getRoutingKey()
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
        $this->initializeConsumer($queue, $callback);
        $this->runConsumer($queue);
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

    private function initializeConsumer(Queue $queue, RabbitConsumerCallbackInterface $callback): void
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
    }

    private function runConsumer(Queue $queue): void
    {
        $parameters = $queue->getConsumerParameters();

        $startedAt = \time();
        $consumedMessages = 0;
        while ($this->channel->is_open() && !$this->consumerStopped) {
            $timeout = $this->calculateTimeout($startedAt, $parameters);
            if ($timeout > 0 && $timeout <= 1) {
                $this->stopConsumer();
            }
            $this->channel->wait(null, false, $timeout);
            ++$consumedMessages;
            $this->assertConsumedMessagesLimit($parameters, $consumedMessages);
            $this->assertConsumerTimeLimit($startedAt, $parameters);
        }
    }

    private function assertConsumedMessagesLimit(ConsumerParameters $parameters, int $consumedMessages): void
    {
        if ($parameters->hasMessagesLimit() && $consumedMessages >= $parameters->getMessagesLimit()) {
            $this->stopConsumer();
            throw new MessageLimitException($parameters->getMessagesLimit());
        }
    }

    private function assertConsumerTimeLimit(int $startedAt, ConsumerParameters $parameters): void
    {
        if ($this->timeLimitExceeded($startedAt, $parameters)) {
            $this->stopConsumer();
            throw new TimeLimitException($parameters->getTimeLimit());
        }
    }
}
