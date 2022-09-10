<?php

namespace App\Config;

use Symfony\Component\Yaml\Yaml;

class ConfigFactory
{
    public function __construct(
        private string $filePath
    ) {
    }

    public function create(): Config
    {
        $yaml = Yaml::parseFile($this->filePath)['async_command_bus'] ?? [];
        $connections = $this->readConnections($yaml);
        $exchanges = $this->readExchanges($yaml, $connections);
        $queues = $this->readQueues($yaml, $connections);
        $bindings = $this->readBindings($yaml, $exchanges, $queues);
        $commands = $this->readCommands($yaml, $exchanges, $queues);

        return new Config($exchanges, $queues, $bindings, $commands);
    }

    private function readConnections(array $yaml): ConnectionsMap
    {
        $map = new ConnectionsMap();
        foreach ($yaml['connections'] ?? [] as $name => $params) {
            $map->put(
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

        return $map;
    }

    private function readExchanges(array $yaml, ConnectionsMap $connections): ExchangesMap
    {
        $map = new ExchangesMap();
        foreach ($yaml['exchanges'] ?? [] as $name => $params) {
            $map->put(
                $name,
                new Exchange(
                    $params['name'],
                    ExchangeType::from($params['type']),
                    $connections->get($params['connection']),
                    (bool)($params['delayed_active'] ?? false)
                )
            );
        }

        return $map;
    }

    private function readQueues(array $yaml, ConnectionsMap $connections): QueuesMap
    {
        $map = new QueuesMap();
        $map->put(
            'default',
            new Queue(
                'async_command_bus',
                $connections->get('default')
            )
        );
        foreach ($yaml['queues'] ?? [] as $name => $params) {
            $map->put(
                $name,
                new Queue(
                    $params['name'],
                    $connections->get($params['connection']),
                    $params['passive'] ?? false,
                    $params['durable'] ?? false,
                    $params['exclusive'] ?? false,
                    $params['auto_delete'] ?? false
                )
            );
        }

        return $map;
    }

    private function readBindings(array $yaml, ExchangesMap $exchanges, QueuesMap $queues): BindingsMap
    {
        $map = new BindingsMap();
        foreach ($yaml['bindings'] ?? [] as $name => $params) {
            $map->put(
                $name,
                new Binding(
                    $queues->get($params['queue']),
                    $exchanges->get($params['exchange']),
                    $params['routing_key'] ?? ''
                )
            );
        }

        return $map;
    }

    private function readCommands(array $yaml, ExchangesMap $exchanges, QueuesMap $queues): CommandPublisherConfigsMap
    {
        $map = new CommandPublisherConfigsMap();
        foreach ($yaml['commands'] ?? [] as $class => $params) {
            $commandConfig = null;
            $publisherConfig = $params['publisher'] ?? [];
            if (\array_key_exists('queue', $publisherConfig)) {
                $commandConfig = new QueuePublishedCommandConfig($class, $queues->get($publisherConfig['queue']));
            } elseif(\array_key_exists('exchange', $publisherConfig)) {
                $exchangePublisherConfig = $publisherConfig['exchange'];
                $commandConfig = new ExchangePublishedCommandConfig(
                    $class,
                    $exchanges->get($exchangePublisherConfig['name']),
                    $exchangePublisherConfig['routing_key'] ?? ''
                );
            }

            $map->put($commandConfig ?? new QueuePublishedCommandConfig($class, $queues->get('default')));
        }

        return $map;
    }
}
