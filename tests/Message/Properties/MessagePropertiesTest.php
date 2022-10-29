<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Tests\Message\Properties;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Message\Properties\DeliveryMode;
use PHPUnit\Framework\TestCase;

class MessagePropertiesTest extends TestCase
{
    public function testShouldCreateMessageProperties(): void
    {
        $messageProperties = MessageProperties::builder()
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

        self::assertEquals('json', $messageProperties->contentType());
        self::assertEquals('UTF-8', $messageProperties->contentEncoding());
        self::assertEquals(DeliveryMode::Persistent->value, $messageProperties->deliveryMode());
        self::assertEquals(1000, $messageProperties->priority());
        self::assertEquals('aassdd', $messageProperties->correlationId());
        self::assertEquals('test-reply', $messageProperties->replyTo());
        self::assertEquals(100, $messageProperties->expiration());
        self::assertEquals('test-message-id', $messageProperties->messageId());
        self::assertEquals(1234567890, $messageProperties->timestamp());
        self::assertEquals('1234567', $messageProperties->userId());
        self::assertEquals('4567', $messageProperties->appId());
        self::assertEquals('test-cluster-id', $messageProperties->clusterId());

        $headers = $messageProperties->headers();
        $expectedHeaders = [
            'first-header' => 'first-header-value',
            'second-header' => 'second-header-value'
        ];
        self::assertEquals($expectedHeaders, $headers);
    }
}
