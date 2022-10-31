<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Tests\Config;

use Siemieniec\AmqpMessageBus\Config\ConfigFactory;
use Siemieniec\AmqpMessageBus\Config\Connection;
use Siemieniec\AmqpMessageBus\Exception\MissingConnectionException;
use Siemieniec\AmqpMessageBus\Exception\MissingQueueException;
use Siemieniec\AmqpMessageBus\Serializer\DefaultMessageSerializer;
use PHPUnit\Framework\TestCase;

class ConfigFactoryTest extends TestCase
{
    public function testShouldCreateDefaultConfigWithMinimalParameters(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ]
        ];

        $messageClass = 'TestCommand';
        $config = $this->getConfigFactory()->create($data);

        $messageConfig = $config->getMessageConfig($messageClass);
        self::assertEquals($messageClass, $messageConfig->getMessageClass());
        self::assertEquals(DefaultMessageSerializer::class, $messageConfig->getSerializerClass());

        $publisherConfig = $messageConfig->getPublisherConfig();
        self::assertEquals('amqp_message_bus', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('default', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('/', $publisherConnectionConfig->getVHost());
        self::assertEquals(Connection::DEFAULT_INSIST, $publisherConnectionConfig->isInsist());
        self::assertEquals(Connection::DEFAULT_LOGIN_METHOD, $publisherConnectionConfig->getLoginMethod());
        self::assertEquals(Connection::DEFAULT_LOCALE, $publisherConnectionConfig->getLocale());
        self::assertEquals(Connection::DEFAULT_CONNECTION_TIMEOUT, $publisherConnectionConfig->getConnectionTimeout());
        self::assertEquals(Connection::DEFAULT_READ_WRITE_TIMEOUT, $publisherConnectionConfig->getReadWriteTimeout());
        self::assertEquals(Connection::DEFAULT_KEEP_ALIVE, $publisherConnectionConfig->isKeepAlive());
        self::assertEquals(Connection::DEFAULT_HEARTBEAT, $publisherConnectionConfig->getHeartbeat());
        self::assertEquals(Connection::DEFAULT_SSL_PROTOCOL, $publisherConnectionConfig->getSslProtocol());

        $defaultQueueConfig = $config->getQueueConfig('default');
        self::assertEquals('amqp_message_bus', $defaultQueueConfig->getName());
        self::assertFalse($defaultQueueConfig->isPassive());
        self::assertFalse($defaultQueueConfig->isDurable());
        self::assertFalse($defaultQueueConfig->isAutoDelete());
        self::assertFalse($defaultQueueConfig->isExclusive());
        self::assertFalse($defaultQueueConfig->canAutoDeclare());
        self::assertEquals($publisherConnectionConfig, $defaultQueueConfig->getConnection());
        self::assertTrue($publisherConnectionConfig->equals($defaultQueueConfig->getConnection()));
    }

    public function testShouldOverwriteDefaultQueueConfig(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'default' => [
                    'name' => 'default_queue',
                    'passive' => true,
                    'durable' => true,
                    'exclusive' => true,
                    'auto_delete' => true,
                    'auto_declare' => true
                ]
            ],
        ];

        $messageClass = 'TestCommand';
        $config = $this->getConfigFactory()->create($data);

        $publisherConfig = $config->getMessageConfig($messageClass)->getPublisherConfig();
        self::assertEquals('default_queue', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();

        $defaultQueueConfig = $config->getQueueConfig('default');
        self::assertEquals('default_queue', $defaultQueueConfig->getName());
        self::assertTrue($defaultQueueConfig->isPassive());
        self::assertTrue($defaultQueueConfig->isDurable());
        self::assertTrue($defaultQueueConfig->isAutoDelete());
        self::assertTrue($defaultQueueConfig->isExclusive());
        self::assertTrue($defaultQueueConfig->canAutoDeclare());
        self::assertEquals($publisherConnectionConfig, $defaultQueueConfig->getConnection());
        self::assertTrue($publisherConnectionConfig->equals($defaultQueueConfig->getConnection()));

        $defaultQueueConnection = $defaultQueueConfig->getConnection();
        self::assertEquals('default', $defaultQueueConnection->getName());
        self::assertEquals('localhost', $defaultQueueConnection->getHost());
        self::assertEquals(5672, $defaultQueueConnection->getPort());
        self::assertEquals('guest', $defaultQueueConnection->getUser());
        self::assertEquals('guest', $defaultQueueConnection->getPassword());
        self::assertEquals('/', $defaultQueueConnection->getVHost());
    }

    public function testShouldDefineCommandPublishedToCustomQueue(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ],
                'second' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest',
                    'vhost' => 'test_vhost',
                    'insist' => true,
                    'login_method' => 'PLAIN',
                    'locale' => 'pl_PL',
                    'connection_timeout' => 5.0,
                    'read_write_timeout' => 11.1,
                    'keep_alive' => true,
                    'heartbeat' => 1234,
                    'ssl_protocol' => 'test'
                ]
            ],
            'queues' => [
                'custom_queue_name' => [
                    'connection' => 'second',
                    'name' => 'custom_queue'
                ]
            ],
            'messages' => [
                'TestCommand' => [
                    'serializer' => 'TestCommandSerializer',
                    'publisher' => [
                        'queue' => 'custom_queue_name'
                    ]
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $messageConfig = $config->getMessageConfig('TestCommand');
        self::assertEquals('TestCommand', $messageConfig->getMessageClass());
        self::assertEquals('TestCommandSerializer', $messageConfig->getSerializerClass());
        self::assertFalse($messageConfig->requeueOnFailure());

        $publisherConfig = $messageConfig->getPublisherConfig();
        self::assertEquals('custom_queue', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('second', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('test_vhost', $publisherConnectionConfig->getVHost());
        self::assertEquals(true, $publisherConnectionConfig->isInsist());
        self::assertEquals('PLAIN', $publisherConnectionConfig->getLoginMethod());
        self::assertEquals('pl_PL', $publisherConnectionConfig->getLocale());
        self::assertEquals(5.0, $publisherConnectionConfig->getConnectionTimeout());
        self::assertEquals(11.1, $publisherConnectionConfig->getReadWriteTimeout());
        self::assertEquals(true, $publisherConnectionConfig->isKeepAlive());
        self::assertEquals(1234, $publisherConnectionConfig->getHeartbeat());
        self::assertEquals('test', $publisherConnectionConfig->getSslProtocol());
    }

    public function testShouldDefineCommandPublishedToExchangeBoundToDefaultQueue(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'exchanges' => [
                'test_exchange_name' => [
                    'name' => 'test_exchange',
                    'queue_bindings' => [
                        [
                            'queue' => 'default',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ],
            'messages' => [
                'TestCommand' => [
                    'requeue_on_failure' => true,
                    'publisher' => [
                        'exchange' => [
                            'name' => 'test_exchange_name',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $messageConfig = $config->getMessageConfig('TestCommand');
        self::assertEquals('TestCommand', $messageConfig->getMessageClass());
        self::assertEquals(DefaultMessageSerializer::class, $messageConfig->getSerializerClass());
        self::assertTrue($messageConfig->requeueOnFailure());

        $publisherConfig = $messageConfig->getPublisherConfig();
        self::assertEquals('test_routing_key', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('test_exchange', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('default', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('/', $publisherConnectionConfig->getVHost());

        $exchange = $config->getAllExchanges()['test_exchange_name'];
        self::assertEquals('test_exchange', $exchange->getName());
        self::assertEquals('direct', $exchange->getType());
        self::assertFalse($exchange->canAutoDeclare());

        $binding = $exchange->getQueueBindings()[0];
        self::assertEquals('amqp_message_bus', $binding->getQueue()->getName());
        self::assertEquals('test_routing_key', $binding->getRoutingKey());
    }

    public function testShouldDefineCommandPublishedToExchangeBoundToCustomQueue(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'custom_queue_name' => [
                    'name' => 'custom_queue'
                ]
            ],
            'exchanges' => [
                'test_exchange_name' => [
                    'name' => 'test_exchange',
                    'auto_declare' => true,
                    'queue_bindings' => [
                        [
                            'queue' => 'custom_queue_name',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ],
            'messages' => [
                'TestCommand' => [
                    'publisher' => [
                        'exchange' => [
                            'name' => 'test_exchange_name',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $messageConfig = $config->getMessageConfig('TestCommand');
        self::assertEquals('TestCommand', $messageConfig->getMessageClass());
        self::assertEquals(DefaultMessageSerializer::class, $messageConfig->getSerializerClass());

        $publisherConfig = $messageConfig->getPublisherConfig();
        self::assertEquals('test_routing_key', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('test_exchange', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('default', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('/', $publisherConnectionConfig->getVHost());

        $exchange = $config->getAllExchanges()['test_exchange_name'];
        self::assertEquals('test_exchange', $exchange->getName());
        self::assertEquals('direct', $exchange->getType());
        self::assertFalse($exchange->isInternal());
        self::assertFalse($exchange->isAutoDelete());
        self::assertFalse($exchange->isDurable());
        self::assertFalse($exchange->isPassive());
        self::assertFalse($exchange->hasArguments());
        self::assertTrue($exchange->canAutoDeclare());

        $binding = $exchange->getQueueBindings()[0];
        self::assertEquals('custom_queue', $binding->getQueue()->getName());
        self::assertEquals('test_routing_key', $binding->getRoutingKey());
    }

    public function testShouldThrowExceptionWhenTryingToBindExchangeWithNotDefinedQueue(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ],
                'second' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest',
                    'vhost' => 'test_vhost'
                ]
            ],
            'exchanges' => [
                'test_exchange_name' => [
                    'name' => 'test_exchange',
                    'queue_bindings' => [
                        [
                            'queue' => 'custom_queue_name',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ]
        ];

        self::expectException(MissingQueueException::class);
        self::expectExceptionMessage('Queue custom_queue_name has not been defined');
        $this->getConfigFactory()->create($data);
    }

    public function testShouldThrowMissingConnectionException(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'custom_queue_name' => [
                    'connection' => 'second',
                    'name' => 'custom_queue'
                ]
            ]
        ];

        self::expectException(MissingConnectionException::class);
        self::expectExceptionMessage('Connection second has not been defined');
        $this->getConfigFactory()->create($data);
    }

    public function testShouldCreateDefaultQueueConsumerConfig(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $queueConfig = $config->getQueueConfig('default');
        $consumerConfig = $queueConfig->getConsumerParameters();
        self::assertEquals('', $consumerConfig->getTag());
        self::assertTrue($consumerConfig->isAck());
        self::assertFalse($consumerConfig->isExclusive());
        self::assertTrue($consumerConfig->isLocal());
        self::assertEquals(1, $consumerConfig->getPrefetchCount());
        self::assertEquals(0, $consumerConfig->getTimeLimit());
        self::assertEquals(0, $consumerConfig->getWaitTimeout());
        self::assertEquals(0, $consumerConfig->getMessagesLimit());
    }

    public function testShouldCustomQueueHaveDefaultConsumerConfig(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'custom_queue_name' => [
                    'name' => 'custom_queue'
                ]
            ],
        ];

        $config = $this->getConfigFactory()->create($data);
        $queueConfig = $config->getQueueConfig('custom_queue_name');
        $consumerConfig = $queueConfig->getConsumerParameters();
        self::assertEquals('', $consumerConfig->getTag());
        self::assertTrue($consumerConfig->isAck());
        self::assertFalse($consumerConfig->isExclusive());
        self::assertTrue($consumerConfig->isLocal());
        self::assertEquals(1, $consumerConfig->getPrefetchCount());
        self::assertEquals(0, $consumerConfig->getTimeLimit());
        self::assertEquals(0, $consumerConfig->getWaitTimeout());
        self::assertEquals(0, $consumerConfig->getMessagesLimit());
    }

    public function testShouldOverwriteDefaultValuesOfConsumerConfigForDefaultConfig(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'default' => [
                    'consumer' => [
                        'tag' => 'test_tag',
                        'ack' => false,
                        'exclusive' => true,
                        'local' => false,
                        'prefetch_count' => 10,
                        'time_limit' => 11,
                        'wait_timeout' => 12,
                        'messages_limit' => 1000
                    ]
                ]
            ],
        ];

        $config = $this->getConfigFactory()->create($data);
        $queueConfig = $config->getQueueConfig('default');
        $consumerConfig = $queueConfig->getConsumerParameters();
        self::assertEquals('test_tag', $consumerConfig->getTag());
        self::assertFalse($consumerConfig->isAck());
        self::assertTrue($consumerConfig->isExclusive());
        self::assertFalse($consumerConfig->isLocal());
        self::assertEquals(10, $consumerConfig->getPrefetchCount());
        self::assertEquals(11, $consumerConfig->getTimeLimit());
        self::assertEquals(12, $consumerConfig->getWaitTimeout());
        self::assertEquals(1000, $consumerConfig->getMessagesLimit());
    }

    public function testShouldCreateQueueArguments(): void
    {
        $definedArguments = [
            'x-expires' => 100,
            'x-message-ttl' => 200,
            'x-overflow' => 'drop-head',
            'x-single-active-consumer' => true,
            'x-dead-letter-exchange' => 'test_exchange',
            'x-dead-letter-routing-key' => 'test_routing_key',
            'x-max-length' => 1000,
            'x-max-length-bytes' => 2048,
            'x-max-priority' => 10,
            'x-queue-mode' => 'lazy',
            'x-queue-version' => 2,
            'x-queue-master-locator' => 'something'
        ];
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'custom_queue_name' => [
                    'arguments' => $definedArguments
                ]
            ],
        ];

        $config = $this->getConfigFactory()->create($data);
        $queueConfig = $config->getQueueConfig('custom_queue_name');
        self::assertEquals($definedArguments, $queueConfig->getArguments());
        self::assertTrue($queueConfig->hasArguments());
    }

    public function testShouldCreateDelayedExchangeConfig(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'exchanges' => [
                'test_exchange_name' => [
                    'name' => 'test_exchange',
                    'type' => 'x-delayed-message',
                    'passive' => true,
                    'durable' => true,
                    'auto_delete' => true,
                    'internal' => true,
                    'arguments' => [
                        'x-delayed-type' => 'direct'
                    ]
                ]
            ]
        ];
        $config = $this->getConfigFactory()->create($data);
        $exchange = $config->getAllExchanges()['test_exchange_name'];
        self::assertEquals('default', $exchange->getConnection()->getName());
        self::assertEquals('test_exchange', $exchange->getName());
        self::assertEquals('x-delayed-message', $exchange->getType());
        self::assertTrue($exchange->hasArguments());
        self::assertEquals(['x-delayed-type' => 'direct'], $exchange->getArguments());
        self::assertTrue($exchange->isPassive());
        self::assertTrue($exchange->isDurable());
        self::assertTrue($exchange->isAutoDelete());
        self::assertTrue($exchange->isInternal());
    }

    public function testShouldEnableAutoDeclareByGlobalSetting(): void
    {
        $data = [
            'auto_declare' => true,
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'exchanges' => [
                'test_exchange_name' => [
                    'name' => 'test_exchange',
                    'queue_bindings' => [
                        [
                            'queue' => 'default',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ],
            'messages' => [
                'TestCommand' => [
                    'publisher' => [
                        'exchange' => [
                            'name' => 'test_exchange_name',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $queue = $config->getQueueConfig('default');
        self::assertTrue($queue->canAutoDeclare());
        $exchange = $config->getAllExchanges()['test_exchange_name'];
        self::assertTrue($exchange->canAutoDeclare());
    }

    public function testShouldLocalAutoDeclareOverwriteGlobalSettings(): void
    {
        $data = [
            'auto_declare' => true,
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'default' => [
                    'auto_declare' => false
                ]
            ],
            'exchanges' => [
                'test_exchange_name' => [
                    'name' => 'test_exchange',
                    'auto_declare' => false,
                    'queue_bindings' => [
                        [
                            'queue' => 'default',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ],
            'messages' => [
                'TestCommand' => [
                    'publisher' => [
                        'exchange' => [
                            'name' => 'test_exchange_name',
                            'routing_key' => 'test_routing_key'
                        ]
                    ]
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $queue = $config->getQueueConfig('default');
        self::assertFalse($queue->canAutoDeclare());
        $exchange = $config->getAllExchanges()['test_exchange_name'];
        self::assertFalse($exchange->canAutoDeclare());
    }

    public function testShouldCastConnectionConfigValues(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => '5672',
                    'user' => 'guest',
                    'password' => 'guest',
                    'insist' => 'true',
                    'login_method' => 'PLAIN',
                    'locale' => 'pl_PL',
                    'connection_timeout' => '5,0',
                    'read_write_timeout' => '11,1',
                    'keep_alive' => 'true',
                    'heartbeat' => '1234',
                    'ssl_protocol' => 'test'
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $connection = $config->getMessageConfig('TestCommand')->getPublisherConfig()->getConnection();
        self::assertEquals(5672, $connection->getPort());
        self::assertEquals(true, $connection->isInsist());
        self::assertEquals('PLAIN', $connection->getLoginMethod());
        self::assertEquals('pl_PL', $connection->getLocale());
        self::assertEquals(5.0, $connection->getConnectionTimeout());
        self::assertEquals(11.1, $connection->getReadWriteTimeout());
        self::assertEquals(true, $connection->isKeepAlive());
        self::assertEquals(1234, $connection->getHeartbeat());
        self::assertEquals('test', $connection->getSslProtocol());
    }

    public function testShouldCastQueueConfigValues(): void
    {
        $data = [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => '5672',
                    'user' => 'guest',
                    'password' => 'guest'
                ]
            ],
            'queues' => [
                'default' => [
                    'passive' => 'true',
                    'durable' => 'true',
                    'exclusive' => 'false',
                    'auto_delete' => 'false',
                    'auto_declare' => 'true',
                    'consumer' => [
                        'ack' => 'false',
                        'exclusive' => 'true',
                        'local' => 'false',
                        'prefetch_count' => '10',
                        'time_limit' => '11',
                        'wait_timeout' => '12',
                        'messages_limit' => '1000'
                    ]
                ],
                'second' => [
                    'passive' => 1,
                    'durable' => 1,
                    'exclusive' => 0,
                    'auto_delete' => 0,
                    'auto_declare' => 1,
                    'consumer' => [
                        'ack' => 0,
                        'exclusive' => 1,
                        'local' => 0,
                        'prefetch_count' => '10',
                        'time_limit' => '11',
                        'wait_timeout' => '12',
                        'messages_limit' => '1000'
                    ]
                ],
                'third' => [
                    'passive' => '1',
                    'durable' => '1',
                    'exclusive' => '0',
                    'auto_delete' => '0',
                    'auto_declare' => '1',
                    'consumer' => [
                        'ack' => '0',
                        'exclusive' => '1',
                        'local' => '0',
                        'prefetch_count' => '10',
                        'time_limit' => '11',
                        'wait_timeout' => '12',
                        'messages_limit' => '1000'
                    ]
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        foreach ($config->getAllQueues() as $queue) {
            self::assertTrue($queue->isPassive(), 'passive');
            self::assertTrue($queue->isDurable(), 'durable');
            self::assertFalse($queue->isExclusive(), 'exclusive queue');
            self::assertFalse($queue->isAutoDelete(), 'auto_delete');
            self::assertTrue($queue->canAutoDeclare(), 'auto_declare');

            $consumer = $queue->getConsumerParameters();
            self::assertFalse($consumer->isAck(), 'ack');
            self::assertTrue($consumer->isExclusive(), 'exclusive consumer');
            self::assertFalse($consumer->isLocal(), 'local');
            self::assertEquals(10, $consumer->getPrefetchCount(), 'prefetch count');
            self::assertEquals(11, $consumer->getTimeLimit(), 'time limit');
            self::assertEquals(12, $consumer->getWaitTimeout(), 'wait timeout');
            self::assertEquals(1000, $consumer->getMessagesLimit(), 'messages limit');
        }
    }

    private function getConfigFactory(): ConfigFactory
    {
        return new ConfigFactory();
    }
}
