<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPConnectionBlockedException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Psr\Log\LoggerInterface;
use Siemieniec\AmqpMessageBus\Config\Connection;
use Siemieniec\AmqpMessageBus\Config\ConnectionCredentials;
use Siemieniec\AmqpMessageBus\Config\ConsumerParameters;
use Siemieniec\AmqpMessageBus\Config\Exchange;
use Siemieniec\AmqpMessageBus\Config\PublisherTarget;
use Siemieniec\AmqpMessageBus\Config\Queue;
use Siemieniec\AmqpMessageBus\Config\QueueBinding;
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
    private ?string $consumerTag = null;

    public function __construct(Connection $connectionConfig, private LoggerInterface $logger)
    {
        $this->connection = AMQPStreamConnection::create_connection(
            \array_map(
                fn(ConnectionCredentials $connectionCredentials): array => [
                    'host' => $connectionCredentials->getHost(),
                    'port' => $connectionCredentials->getPort(),
                    'user' => $connectionCredentials->getUser(),
                    'password' => $connectionCredentials->getPassword(),
                    'vhost' => $connectionCredentials->getVHost()
                ],
                $connectionConfig->getConnectionCredentials()
            ),
            [
                'insist' => $connectionConfig->isInsist(),
                'login_method' => $connectionConfig->getLoginMethod(),
                'locale' => $connectionConfig->getLocale(),
                'connection_timeout' => $connectionConfig->getConnectionTimeout(),
                'read_write_timeout' => $connectionConfig->getReadWriteTimeout(),
                'keepalive' => $connectionConfig->isKeepAlive(),
                'heartbeat' => $connectionConfig->getHeartbeat(),
                'ssl_protocol' => $connectionConfig->getSslProtocol()
            ]
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
        $this->consumerStopped = true;
        if (!empty($this->consumerTag)) {
            $this->channel->basic_cancel($this->consumerTag, false, true);
            $this->consumerTag = null;
        }
    }

    private function calculateTimeout(int $startedAt, ConsumerParameters $parameters): int
    {
        $timeout = $parameters->getWaitTimeout();
        if ($parameters->hasTimeLimit()) {
            $finishAt = $startedAt + $parameters->getTimeLimit();
            $finishTimeout = $finishAt - \time();
            $timeout = \min($timeout, $finishTimeout);
        }

        return \max($timeout, 1);
    }

    private function timeLimitExceeded(int $startedAt, ConsumerParameters $parameters): bool
    {
        return $parameters->hasTimeLimit() && \time() >= $startedAt + $parameters->getTimeLimit();
    }

    private function initializeConsumer(Queue $queue, RabbitConsumerCallbackInterface $callback): void
    {
        $this->consumerStopped = false;
        $parameters = $queue->getConsumerParameters();

        $this->channel->basic_qos(0, $queue->getConsumerParameters()->getPrefetchCount(), false);
        $this->consumerTag = $this->channel->basic_consume(
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
        $this->checkConnection();

        $parameters = $queue->getConsumerParameters();
        $startedAt = \time();
        $consumedMessages = 0;

        while ($this->channel->is_consuming()) {
            if ($this->consumerStopped) {
                $this->consumerStopped = false;
                return;
            }
            $this->assertConsumedMessagesLimit($parameters, $consumedMessages);
            $this->assertConsumerTimeLimit($startedAt, $parameters);
            $timeout = $this->calculateTimeout($startedAt, $parameters);
            try {
                $this->channel->wait(null, false, $timeout);
                ++$consumedMessages;
            } catch (AMQPTimeoutException) {
                $this->connection->checkHeartBeat();
                continue;
            }
        }
    }

    private function assertConsumedMessagesLimit(ConsumerParameters $parameters, int $consumedMessages): void
    {
        if ($parameters->hasMessagesLimit() && $consumedMessages >= $parameters->getMessagesLimit()) {
            $this->logger->warning(
                \sprintf(
                    'Limit of %d messages has been reached. Stopping consumer...',
                    $parameters->getMessagesLimit()
                )
            );
            $this->stopConsumer();
        }
    }

    private function assertConsumerTimeLimit(int $startedAt, ConsumerParameters $parameters): void
    {
        if ($this->timeLimitExceeded($startedAt, $parameters)) {
            $this->logger->warning(
                \sprintf(
                    'Time limit of %d seconds has been reached. Stopping consumer...',
                    $parameters->getTimeLimit()
                )
            );
            $this->stopConsumer();
        }
    }

    private function checkConnection(): void
    {
        if (!$this->connection->isConnected()) {
            throw new AMQPChannelClosedException('Channel connection is closed.');
        }
        if ($this->connection->isBlocked()) {
            throw new AMQPConnectionBlockedException();
        }
    }
}
