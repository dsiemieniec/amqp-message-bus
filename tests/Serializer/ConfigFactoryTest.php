<?php

namespace App\Tests\Serializer;

use App\Config\ConfigFactory;
use App\Serializer\DefaultCommandSerializer;
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
}
