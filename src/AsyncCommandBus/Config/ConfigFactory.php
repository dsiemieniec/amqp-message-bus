<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Config\Connection;
use Siemieniec\AsyncCommandBus\Config\ConnectionsMap;
use Siemieniec\AsyncCommandBus\Config\ConsumerParameters;
use Siemieniec\AsyncCommandBus\Config\Exchange;
use Siemieniec\AsyncCommandBus\Config\ExchangePublishedCommandConfig;
use Siemieniec\AsyncCommandBus\Config\ExchangesMap;
use Siemieniec\AsyncCommandBus\Config\Queue;
use Siemieniec\AsyncCommandBus\Config\QueuePublishedCommandConfig;
use Siemieniec\AsyncCommandBus\Config\QueuesMap;
use Siemieniec\AsyncCommandBus\Config\CommandConfig;
use Siemieniec\AsyncCommandBus\Config\CommandConfigsMap;
use Siemieniec\AsyncCommandBus\Config\Config;
use Siemieniec\AsyncCommandBus\Serializer\DefaultCommandSerializer;
use Siemieniec\AsyncCommandBus\Utils\Inputs;

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

    private function initMaps(): void
    {
        $this->connections = new ConnectionsMap();
        $this->exchanges = new ExchangesMap();
        $this->queues = new QueuesMap();
        $this->commands = new CommandConfigsMap();
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
        $globalAutoDeclare = Inputs::boolValue($this->config['auto_declare'] ?? Exchange::DEFAULT_AUTO_DECLARE);
        foreach ($this->config['exchanges'] ?? [] as $name => $params) {
            $this->exchanges[$name] = new Exchange(
                name: $params['name'],
                type: $params['type'] ?? Exchange::TYPE_DIRECT,
                connection: $this->connections[$params['connection'] ?? Connection::DEFAULT_CONNECTION_NAME],
                passive: Inputs::boolValue($params['passive'] ?? Exchange::DEFAULT_PASSIVE),
                durable: Inputs::boolValue($params['durable'] ?? Exchange::DEFAULT_DURABLE),
                autoDelete: Inputs::boolValue($params['auto_delete'] ?? Exchange::DEFAULT_AUTO_DELETE),
                internal: Inputs::boolValue($params['internal'] ?? Exchange::DEFAULT_INTERNAL),
                autoDeclare: Inputs::boolValue($params['auto_declare'] ?? $globalAutoDeclare),
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
        $globalAutoDeclare = Inputs::boolValue($this->config['auto_declare'] ?? Queue::DEFAULT_AUTO_DECLARE);
        foreach ($this->config['queues'] ?? [] as $name => $params) {
            $queueName = $params['name'] ?? ($name === 'default' ? self::DEFAULT_QUEUE_NAME : $name);
            $consumerConfig = $params['consumer'] ?? [];

            $this->queues[$name] = new Queue(
                name: $queueName,
                connection: $this->connections[$params['connection'] ?? Connection::DEFAULT_CONNECTION_NAME],
                consumerParameters: new ConsumerParameters(
                    tag: $consumerConfig['tag'] ?? ConsumerParameters::DEFAULT_TAG,
                    ack: Inputs::boolValue($consumerConfig['ack'] ?? ConsumerParameters::DEFAULT_ACK),
                    exclusive: Inputs::boolValue(
                        $consumerConfig['exclusive'] ?? ConsumerParameters::DEFAULT_EXCLUSIVE
                    ),
                    local: Inputs::boolValue($consumerConfig['local'] ?? ConsumerParameters::DEFAULT_LOCAL),
                    prefetchCount: (int) (
                        $consumerConfig['prefetch_count'] ?? ConsumerParameters::DEFAULT_PREFETCH_COUNT
                    ),
                    timeLimit: (int) ($consumerConfig['time_limit'] ?? ConsumerParameters::NO_LIMIT),
                    waitTimeout: (int) ($consumerConfig['wait_timeout'] ?? ConsumerParameters::NO_LIMIT),
                    messagesLimit: (int) ($consumerConfig['messages_limit'] ?? ConsumerParameters::NO_LIMIT)
                ),
                passive: Inputs::boolValue($params['passive'] ?? Queue::DEFAULT_PASSIVE),
                durable: Inputs::boolValue($params['durable'] ?? Queue::DEFAULT_DURABLE),
                exclusive: Inputs::boolValue($params['exclusive'] ?? Queue::DEFAULT_EXCLUSIVE),
                autoDelete: Inputs::boolValue($params['auto_delete'] ?? Queue::DEFAULT_AUTO_DELETE),
                autoDeclare: Inputs::boolValue($params['auto_declare'] ?? $globalAutoDeclare),
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
                Inputs::boolValue($params['requeue_on_failure'] ?? CommandConfig::DEFAULT_REQUEUE_ON_FAILURE)
            );
        }
    }
}
