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

        $publisherConnectionConfig = $commandConfig->getPublisherConfig()->getConnection();
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
}
