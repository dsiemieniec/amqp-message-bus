<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

use Exception;
use Siemieniec\AmqpMessageBus\Serializer\DefaultMessageSerializer;
use Siemieniec\AmqpMessageBus\Utils\Inputs;

final class ConfigFactory
{
    private const DEFAULT_QUEUE_NAME = 'amqp_message_bus';

    /** @var array<string, mixed> */
    private array $config = [];
    private ConnectionsMap $connections;
    private ExchangesMap $exchanges;
    private QueuesMap $queues;
    private MessageConfigsMap $messages;

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
        $this->readMessages();

        return new Config($this->exchanges, $this->queues, $this->messages);
    }

    private function initMaps(): void
    {
        $this->connections = new ConnectionsMap();
        $this->exchanges = new ExchangesMap();
        $this->queues = new QueuesMap();
        $this->messages = new MessageConfigsMap();
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
                vHost: $params['vhost'] ?? Connection::DEFAULT_VHOST,
                insist: Inputs::boolValue($params['insist'] ?? Connection::DEFAULT_INSIST),
                loginMethod: $params['login_method'] ?? Connection::DEFAULT_LOGIN_METHOD,
                locale: $params['locale'] ?? Connection::DEFAULT_LOCALE,
                connectionTimeout: Inputs::floatValue(
                    $params['connection_timeout'] ?? Connection::DEFAULT_CONNECTION_TIMEOUT
                ),
                readWriteTimeout: Inputs::floatValue(
                    $params['read_write_timeout'] ?? Connection::DEFAULT_READ_WRITE_TIMEOUT
                ),
                keepAlive: Inputs::boolValue($params['keep_alive'] ?? Connection::DEFAULT_KEEP_ALIVE),
                heartbeat: (int)($params['heartbeat'] ?? Connection::DEFAULT_HEARTBEAT),
                sslProtocol: $params['ssl_protocol'] ?? Connection::DEFAULT_SSL_PROTOCOL
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

    private function readMessages(): void
    {
        foreach ($this->config['messages'] ?? [] as $class => $params) {
            $publisherConfig = null;
            $publisherConfig = $params['publisher'] ?? [];
            if (\array_key_exists('queue', $publisherConfig)) {
                $publisherConfig = new QueuePublishedMessageConfig($this->queues[$publisherConfig['queue']]);
            } elseif (\array_key_exists('exchange', $publisherConfig)) {
                $exchangePublisherConfig = $publisherConfig['exchange'];
                $publisherConfig = new ExchangePublishedMessageConfig(
                    $this->exchanges[$exchangePublisherConfig['name']],
                    $exchangePublisherConfig['routing_key'] ?? ''
                );
            }

            $this->messages[$class] = new MessageConfig(
                $class,
                $params['serializer'] ?? DefaultMessageSerializer::class,
                $publisherConfig ?? new QueuePublishedMessageConfig($this->queues['default']),
                Inputs::boolValue($params['requeue_on_failure'] ?? MessageConfig::DEFAULT_REQUEUE_ON_FAILURE)
            );
        }
    }
}
