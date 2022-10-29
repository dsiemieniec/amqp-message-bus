<?php

namespace Siemieniec\AsyncCommandBus\Tests\Command\Properties;

use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Command\Properties\DeliveryMode;
use PHPUnit\Framework\TestCase;

class CommandPropertiesTest extends TestCase
{
    public function testShouldCreateCommandProperties(): void
    {
        $commandProperties = CommandProperties::builder()
            ->contentType('json')
            ->contentEncoding('UTF-8')
            ->deliveryMode(DeliveryMode::Persistent)
            ->priority(1000)
            ->correlationId('aassdd')
            ->replyTo('test-reply')
            ->expiration(100)
            ->messageId('test-message-id')
            ->timestamp(1234567890)
            ->userId('1234567')
            ->appId('4567')
            ->clusterId('test-cluster-id')
            ->addHeader('first-header', 'first-header-value')
            ->addHeader('second-header', 'second-header-value')
            ->build();

        self::assertEquals('json', $commandProperties->contentType());
        self::assertEquals('UTF-8', $commandProperties->contentEncoding());
        self::assertEquals(DeliveryMode::Persistent->value, $commandProperties->deliveryMode());
        self::assertEquals(1000, $commandProperties->priority());
        self::assertEquals('aassdd', $commandProperties->correlationId());
        self::assertEquals('test-reply', $commandProperties->replyTo());
        self::assertEquals(100, $commandProperties->expiration());
        self::assertEquals('test-message-id', $commandProperties->messageId());
        self::assertEquals(1234567890, $commandProperties->timestamp());
        self::assertEquals('1234567', $commandProperties->userId());
        self::assertEquals('4567', $commandProperties->appId());
        self::assertEquals('test-cluster-id', $commandProperties->clusterId());

        $headers = $commandProperties->headers();
        $expectedHeaders = [
            'first-header' => 'first-header-value',
            'second-header' => 'second-header-value'
        ];
        self::assertEquals($expectedHeaders, $headers);
    }
}
