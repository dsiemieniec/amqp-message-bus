<?php

declare(strict_types=1);

namespace App\Config;

use App\Serializer\DefaultCommandSerializer;
use Exception;

final class ConfigFactory
{
    private ConnectionsMap $connections;
    private ExchangesMap $exchanges;
    private QueuesMap $queues;
    private BindingsMap $bindings;
    private CommandConfigsMap $commands;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private array $config
    ) {
        $this->connections = new ConnectionsMap();
        $this->exchanges = new ExchangesMap();
        $this->queues = new QueuesMap();
        $this->bindings = new BindingsMap();
        $this->commands = new CommandConfigsMap();
    }

    /**
     * @throws Exception
     */
    public function create(): Config
    {
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
                    $params['name'],
                    ExchangeType::from($params['type']),
                    $this->connections->get($params['connection']),
                    (bool)($params['delayed_active'] ?? false)
                )
            );
        }
    }

    private function readQueues(): void
    {
        $this->queues->put(
            'default',
            new Queue(
                'async_command_bus',
                $this->connections->get('default')
            )
        );
        foreach ($this->config['queues'] ?? [] as $name => $params) {
            $this->queues->put(
                $name,
                new Queue(
                    $params['name'],
                    $this->connections->get($params['connection']),
                    $params['passive'] ?? false,
                    $params['durable'] ?? false,
                    $params['exclusive'] ?? false,
                    $params['auto_delete'] ?? false
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

            $serializerClass = $params['serializer'] ?? null;
            if ($serializerClass === null) {
                $serializerClass = DefaultCommandSerializer::class;
            }

            $this->commands
                ->put(
                    new CommandConfig(
                        $class,
                        $serializerClass,
                        $publisherConfig ?? new QueuePublishedCommandConfig($this->queues->get('default'))
                    )
                );
        }
    }
}
