<?php

namespace App\Tests\Config;

use App\Config\ConfigFactory;
use App\Config\ExchangeType;
use App\Config\QueueArgumentsFactory;
use App\Exception\MissingConnectionException;
use App\Exception\MissingExchangeException;
use App\Exception\MissingQueueException;
use App\Serializer\DefaultCommandSerializer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConfigFactoryTest extends TestCase
{
    private function getConfigFactory(): ConfigFactory
    {
        return new ConfigFactory(new QueueArgumentsFactory());
    }

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

        $commandClass = 'TestCommand';
        $config = $this->getConfigFactory()->create($data);

        $commandConfig = $config->getCommandConfig($commandClass);
        self::assertEquals($commandClass, $commandConfig->getCommandClass());
        self::assertEquals(DefaultCommandSerializer::class, $commandConfig->getSerializerClass());

        $publisherConfig = $commandConfig->getPublisherConfig();
        self::assertEquals('async_command_bus', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('default', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('/', $publisherConnectionConfig->getVHost());

        $defaultQueueConfig = $config->getQueueConfig('default');
        self::assertEquals('async_command_bus', $defaultQueueConfig->getName());
        self::assertFalse($defaultQueueConfig->isPassive());
        self::assertFalse($defaultQueueConfig->isDurable());
        self::assertFalse($defaultQueueConfig->isAutoDelete());
        self::assertFalse($defaultQueueConfig->isExclusive());
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
                    'auto_delete' => true
                ]
            ],
        ];

        $commandClass = 'TestCommand';
        $config = $this->getConfigFactory()->create($data);

        $publisherConfig = $config->getCommandConfig($commandClass)->getPublisherConfig();
        self::assertEquals('default_queue', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();

        $defaultQueueConfig = $config->getQueueConfig('default');
        self::assertEquals('default_queue', $defaultQueueConfig->getName());
        self::assertTrue($defaultQueueConfig->isPassive());
        self::assertTrue($defaultQueueConfig->isDurable());
        self::assertTrue($defaultQueueConfig->isAutoDelete());
        self::assertTrue($defaultQueueConfig->isExclusive());
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
                    'vhost' => 'test_vhost'
                ]
            ],
            'queues' => [
                'custom_queue_name' => [
                    'connection' => 'second',
                    'name' => 'custom_queue'
                ]
            ],
            'commands' => [
                'TestCommand' => [
                    'serializer' => 'TestCommandSerializer',
                    'publisher' => [
                        'queue' => 'custom_queue_name'
                    ]
                ]
            ]
        ];

        $config = $this->getConfigFactory()->create($data);
        $commandConfig = $config->getCommandConfig('TestCommand');
        self::assertEquals('TestCommand', $commandConfig->getCommandClass());
        self::assertEquals('TestCommandSerializer', $commandConfig->getSerializerClass());

        $publisherConfig = $commandConfig->getPublisherConfig();
        self::assertEquals('custom_queue', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('second', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('test_vhost', $publisherConnectionConfig->getVHost());
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
                    'name' => 'test_exchange'
                ]
            ],
            'bindings' => [
                'test_binding' => [
                    'queue' => 'default',
                    'exchange' => 'test_exchange_name',
                    'routing_key' => 'test_routing_key'
                ]
            ],
            'commands' => [
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
        $commandConfig = $config->getCommandConfig('TestCommand');
        self::assertEquals('TestCommand', $commandConfig->getCommandClass());
        self::assertEquals(DefaultCommandSerializer::class, $commandConfig->getSerializerClass());

        $publisherConfig = $commandConfig->getPublisherConfig();
        self::assertEquals('test_routing_key', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('test_exchange', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('default', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('/', $publisherConnectionConfig->getVHost());

        $binding = $config->getAllBindings()[0];
        self::assertEquals($publisherConnectionConfig, $binding->getConnection());
        self::assertEquals('async_command_bus', $binding->getQueue()->getName());
        $exchange = $binding->getExchange();
        self::assertEquals('test_exchange', $exchange->getName());
        self::assertFalse($exchange->isDelayedActive());
        self::assertEquals(ExchangeType::DIRECT, $exchange->getType());
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
                    'name' => 'test_exchange'
                ]
            ],
            'bindings' => [
                'test_binding' => [
                    'queue' => 'custom_queue_name',
                    'exchange' => 'test_exchange_name',
                    'routing_key' => 'test_routing_key'
                ]
            ],
            'commands' => [
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
        $commandConfig = $config->getCommandConfig('TestCommand');
        self::assertEquals('TestCommand', $commandConfig->getCommandClass());
        self::assertEquals(DefaultCommandSerializer::class, $commandConfig->getSerializerClass());

        $publisherConfig = $commandConfig->getPublisherConfig();
        self::assertEquals('test_routing_key', $publisherConfig->getPublisherTarget()->getRoutingKey());
        self::assertEquals('test_exchange', $publisherConfig->getPublisherTarget()->getExchange());

        $publisherConnectionConfig = $publisherConfig->getConnection();
        self::assertEquals('default', $publisherConnectionConfig->getName());
        self::assertEquals('localhost', $publisherConnectionConfig->getHost());
        self::assertEquals(5672, $publisherConnectionConfig->getPort());
        self::assertEquals('guest', $publisherConnectionConfig->getUser());
        self::assertEquals('guest', $publisherConnectionConfig->getPassword());
        self::assertEquals('/', $publisherConnectionConfig->getVHost());

        $binding = $config->getAllBindings()[0];
        self::assertEquals($publisherConnectionConfig, $binding->getConnection());
        self::assertEquals('custom_queue', $binding->getQueue()->getName());
        $exchange = $binding->getExchange();
        self::assertEquals('test_exchange', $exchange->getName());
        self::assertFalse($exchange->isDelayedActive());
        self::assertEquals(ExchangeType::DIRECT, $exchange->getType());
    }

    public function testShouldThrowExceptionWhenTryingToBindQueueAndExchangeWithDifferentConnections(): void
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
            'queues' => [
                'custom_queue_name' => [
                    'connection' => 'second',
                    'name' => 'custom_queue'
                ]
            ],
            'exchanges' => [
                'test_exchange_name' => [
                    'name' => 'test_exchange'
                ]
            ],
            'bindings' => [
                'test_binding' => [
                    'queue' => 'custom_queue_name',
                    'exchange' => 'test_exchange_name',
                    'routing_key' => 'test_routing_key'
                ]
            ]
        ];

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Exchange and queue have different connection settings.');
        $this->getConfigFactory()->create($data);
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
                    'name' => 'test_exchange'
                ]
            ],
            'bindings' => [
                'test_binding' => [
                    'queue' => 'custom_queue_name',
                    'exchange' => 'test_exchange_name',
                    'routing_key' => 'test_routing_key'
                ]
            ]
        ];

        self::expectException(MissingQueueException::class);
        self::expectExceptionMessage('Queue custom_queue_name has not been defined');
        $this->getConfigFactory()->create($data);
    }

    public function testShouldThrowExceptionWhenTryingToBindQueueWithNotDefinedExchange(): void
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
            'bindings' => [
                'test_binding' => [
                    'queue' => 'custom_queue_name',
                    'exchange' => 'test_exchange_name',
                    'routing_key' => 'test_routing_key'
                ]
            ]
        ];

        self::expectException(MissingExchangeException::class);
        self::expectExceptionMessage('Exchange test_exchange_name has not been defined');
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
        $arguments = [];
        foreach ($queueConfig->getArguments() as $argument) {
            $arguments[$argument->getKey()] = $argument->getValue();
        }
        self::assertEquals($definedArguments, $arguments);
    }
}
