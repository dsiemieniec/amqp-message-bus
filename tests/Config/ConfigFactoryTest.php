<?php

namespace App\Tests\Config;

use App\Config\ConfigFactory;
use App\Config\ExchangeType;
use App\Exception\MissingExchangeException;
use App\Exception\MissingQueueException;
use App\Serializer\DefaultCommandSerializer;
use InvalidArgumentException;
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

        $commandClass = 'TestCommand';
        $config = (new ConfigFactory($data))->create();

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
        $config = (new ConfigFactory($data))->create();

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

        $config = (new ConfigFactory($data))->create();
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

        $config = (new ConfigFactory($data))->create();
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

        $config = (new ConfigFactory($data))->create();
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
        (new ConfigFactory($data))->create();
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
        (new ConfigFactory($data))->create();
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
        (new ConfigFactory($data))->create();
    }
}
