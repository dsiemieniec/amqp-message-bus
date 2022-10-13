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
    private BindingsMap $bindings;
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
        $this->bindings = new BindingsMap();
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
        $this->readExchanges();
        $this->readQueues();
        $this->readBindings();
        $this->readCommandPublishers();

        return new Config($this->exchanges, $this->queues, $this->bindings, $this->commands);
    }

    private function readConnections(): void
    {
        foreach ($this->config['connections'] ?? [] as $name => $params) {
            $this->connections->put(
                new Connection(
                    $name,
                    $params['host'],
                    (int)$params['port'],
                    $params['user'],
                    $params['password'],
                    $params['vhost'] ?? '/'
                )
            );
        }
    }

    private function readExchanges(): void
    {
        foreach ($this->config['exchanges'] ?? [] as $name => $params) {
            $this->exchanges->put(
                $name,
                new Exchange(
                    name: $params['name'],
                    type: $params['type'] ?? 'direct',
                    connection: $this->connections->get($params['connection'] ?? 'default'),
                    passive: $params['passive'] ?? Exchange::DEFAULT_PASSIVE,
                    durable: $params['durable'] ?? Exchange::DEFAULT_DURABLE,
                    autoDelete: $params['auto_delete'] ?? Exchange::DEFAULT_AUTO_DELETE,
                    internal: $params['internal'] ?? Exchange::DEFAULT_INTERNAL,
                    arguments: $params['arguments'] ?? []
                )
            );
        }
    }

    private function readQueues(): void
    {
        foreach ($this->config['queues'] ?? [] as $name => $params) {
            $queueName = $params['name'] ?? ($name === 'default' ? self::DEFAULT_QUEUE_NAME : $name);
            $consumerConfig = $params['consumer'] ?? [];

            $this->queues->put(
                $name,
                new Queue(
                    name: $queueName,
                    connection: $this->connections->get($params['connection'] ?? 'default'),
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
                    arguments: $params['arguments'] ?? [],
                )
            );
        }

        if (!$this->queues->has('default')) {
            $this->queues->put(
                'default',
                new Queue(
                    self::DEFAULT_QUEUE_NAME,
                    $this->connections->get('default'),
                    new ConsumerParameters()
                )
            );
        }
    }

    /**
     * @throws Exception
     */
    private function readBindings(): void
    {
        foreach ($this->config['bindings'] ?? [] as $name => $params) {
            $this->bindings->put(
                $name,
                new Binding(
                    $this->queues->get($params['queue']),
                    $this->exchanges->get($params['exchange']),
                    $params['routing_key'] ?? ''
                )
            );
        }
    }

    private function readCommandPublishers(): void
    {
        foreach ($this->config['commands'] ?? [] as $class => $params) {
            $publisherConfig = null;
            $publisherConfig = $params['publisher'] ?? [];
            if (\array_key_exists('queue', $publisherConfig)) {
                $publisherConfig = new QueuePublishedCommandConfig($this->queues->get($publisherConfig['queue']));
            } elseif (\array_key_exists('exchange', $publisherConfig)) {
                $exchangePublisherConfig = $publisherConfig['exchange'];
                $publisherConfig = new ExchangePublishedCommandConfig(
                    $this->exchanges->get($exchangePublisherConfig['name']),
                    $exchangePublisherConfig['routing_key'] ?? ''
                );
            }

            $this->commands
                ->put(
                    new CommandConfig(
                        $class,
                        $params['serializer'] ?? DefaultCommandSerializer::class,
                        $publisherConfig ?? new QueuePublishedCommandConfig($this->queues->get('default'))
                    )
                );
        }
    }
}
