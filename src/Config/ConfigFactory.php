<?php

declare(strict_types=1);

namespace App\Config;

use App\Serializer\DefaultCommandSerializer;
use Exception;

final class ConfigFactory
{
    private const DEFAULT_QUEUE_NAME = 'async_command_bus';

    /** @var array<string, mixed> */
    private array $config = [];
    private ConnectionsMap $connections;
    private ExchangesMap $exchanges;
    private QueuesMap $queues;
    private CommandConfigsMap $commands;

    public function __construct()
    {
        $this->initMaps();
    }

    private function initMaps(): void
    {
        $this->connections = new ConnectionsMap();
        $this->exchanges = new ExchangesMap();
        $this->queues = new QueuesMap();
        $this->commands = new CommandConfigsMap();
    }

    /**
     * @param array<string, mixed> $config
     * @throws Exception
     */
    public function create(array $config): Config
    {
        $this->config = $config;
        $this->initMaps();

        $this->readConnections();
        $this->readQueues();
        $this->readExchanges();
        $this->readCommandPublishers();

        return new Config($this->exchanges, $this->queues, $this->commands);
    }

    private function readConnections(): void
    {
        foreach ($this->config['connections'] ?? [] as $name => $params) {
            $this->connections[$name] = new Connection(
                name: $name,
                host: $params['host'],
                port: (int)$params['port'],
                user: $params['user'],
                password: $params['password'],
                vHost: $params['vhost'] ?? Connection::DEFAULT_VHOST
            );
        }
    }

    private function readExchanges(): void
    {
        $globalAutoDeclare = $this->config['auto_declare'] ?? Exchange::DEFAULT_AUTO_DECLARE;
        foreach ($this->config['exchanges'] ?? [] as $name => $params) {
            $this->exchanges[$name] = new Exchange(
                name: $params['name'],
                type: $params['type'] ?? Exchange::TYPE_DIRECT,
                connection: $this->connections[$params['connection'] ?? Connection::DEFAULT_CONNECTION_NAME],
                passive: $params['passive'] ?? Exchange::DEFAULT_PASSIVE,
                durable: $params['durable'] ?? Exchange::DEFAULT_DURABLE,
                autoDelete: $params['auto_delete'] ?? Exchange::DEFAULT_AUTO_DELETE,
                internal: $params['internal'] ?? Exchange::DEFAULT_INTERNAL,
                autoDeclare: $params['auto_declare'] ?? $globalAutoDeclare,
                arguments: $params['arguments'] ?? [],
                queueBindings: \array_map(
                    fn(array $binding): QueueBinding => new QueueBinding(
                        $this->queues[$binding['queue']],
                        $binding['routing_key']
                    ),
                    $params['queue_bindings'] ?? []
                )
            );
        }
    }

    private function readQueues(): void
    {
        $globalAutoDeclare = $this->config['auto_declare'] ?? Queue::DEFAULT_AUTO_DECLARE;
        foreach ($this->config['queues'] ?? [] as $name => $params) {
            $queueName = $params['name'] ?? ($name === 'default' ? self::DEFAULT_QUEUE_NAME : $name);
            $consumerConfig = $params['consumer'] ?? [];

            $this->queues[$name] = new Queue(
                name: $queueName,
                connection: $this->connections[$params['connection'] ?? Connection::DEFAULT_CONNECTION_NAME],
                consumerParameters: new ConsumerParameters(
                    tag: $consumerConfig['tag'] ?? ConsumerParameters::DEFAULT_TAG,
                    ack: $consumerConfig['ack'] ?? ConsumerParameters::DEFAULT_ACK,
                    exclusive: $consumerConfig['exclusive'] ?? ConsumerParameters::DEFAULT_EXCLUSIVE,
                    local: $consumerConfig['local'] ?? ConsumerParameters::DEFAULT_LOCAL,
                    prefetchCount: $consumerConfig['prefetch_count'] ?? ConsumerParameters::DEFAULT_PREFETCH_COUNT,
                    timeLimit: $consumerConfig['time_limit'] ?? ConsumerParameters::NO_LIMIT,
                    waitTimeout: $consumerConfig['wait_timeout'] ?? ConsumerParameters::NO_LIMIT,
                    messagesLimit: $consumerConfig['messages_limit'] ?? ConsumerParameters::NO_LIMIT
                ),
                passive: $params['passive'] ?? Queue::DEFAULT_PASSIVE,
                durable: $params['durable'] ?? Queue::DEFAULT_DURABLE,
                exclusive: $params['exclusive'] ?? Queue::DEFAULT_EXCLUSIVE,
                autoDelete: $params['auto_delete'] ?? Queue::DEFAULT_AUTO_DELETE,
                autoDeclare: $params['auto_declare'] ?? $globalAutoDeclare,
                arguments: $params['arguments'] ?? []
            );
        }

        if (!isset($this->queues['default'])) {
            $this->queues['default'] = new Queue(
                self::DEFAULT_QUEUE_NAME,
                $this->connections['default'],
                new ConsumerParameters(),
                autoDeclare: $globalAutoDeclare
            );
        }
    }

    private function readCommandPublishers(): void
    {
        foreach ($this->config['commands'] ?? [] as $class => $params) {
            $publisherConfig = null;
            $publisherConfig = $params['publisher'] ?? [];
            if (\array_key_exists('queue', $publisherConfig)) {
                $publisherConfig = new QueuePublishedCommandConfig($this->queues[$publisherConfig['queue']]);
            } elseif (\array_key_exists('exchange', $publisherConfig)) {
                $exchangePublisherConfig = $publisherConfig['exchange'];
                $publisherConfig = new ExchangePublishedCommandConfig(
                    $this->exchanges[$exchangePublisherConfig['name']],
                    $exchangePublisherConfig['routing_key'] ?? ''
                );
            }

            $this->commands[$class] = new CommandConfig(
                $class,
                $params['serializer'] ?? DefaultCommandSerializer::class,
                $publisherConfig ?? new QueuePublishedCommandConfig($this->queues['default']),
                $params['requeue_on_failure'] ?? CommandConfig::DEFAULT_REQUEUE_ON_FAILURE
            );
        }
    }
}
