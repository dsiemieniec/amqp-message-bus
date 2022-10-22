<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Tests\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Command\Properties\DeliveryMode;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelope;
use Siemieniec\AsyncCommandBus\Rabbit\MessageTransformer;

final class MessageTransformerTest extends TestCase
{
    public function testShouldTransformMessage(): void
    {
        $body = 'Test message body';
        $commandClass = 'TestCommand';

        $contentType = 'json';
        $contentEncoding = 'UTF-8';
        $deliveryMode = 2;
        $priority = 1000;
        $correlationId = 'aassdd';
        $replyTo = 'test-reply';
        $expiration = 100;
        $messageId = 'test-message-id';
        $timestamp = 1234567890;
        $userId = '1234567';
        $appId = '4567';
        $clusterId = 'test-cluster-id';
        $headers = [
            'first-header' => 'first-header-value',
            'second-header' => 'second-header-value',
        ];

        $properties = [
            'content_type' => $contentType,
            'content_encoding' => $contentEncoding,
            'delivery_mode' => $deliveryMode,
            'priority' => $priority,
            'correlation_id' => $correlationId,
            'reply_to' => $replyTo,
            'expiration' => $expiration,
            'message_id' => $messageId,
            'timestamp' => $timestamp,
            'type' => $commandClass,
            'user_id' => $userId,
            'app_id' => $appId,
            'cluster_id' => $clusterId,
            'application_headers' => new AMQPTable($headers),
        ];

        $message = $this->createMock(AMQPMessage::class);
        $message->method('getBody')->willReturn($body);
        $message->method('get_properties')->willReturn($properties);

        $envelope = $this->getTransformer()->transformMessage($message);
        self::assertEquals($commandClass, $envelope->getCommandClass());
        self::assertEquals($body, $envelope->getBody());

        $properties = $envelope->getProperties();
        self::assertEquals($contentType, $properties->contentType());
        self::assertEquals($contentEncoding, $properties->contentEncoding());
        self::assertEquals($deliveryMode, $properties->deliveryMode());
        self::assertEquals($priority, $properties->priority());
        self::assertEquals($correlationId, $properties->correlationId());
        self::assertEquals($replyTo, $properties->replyTo());
        self::assertEquals($expiration, $properties->expiration());
        self::assertEquals($messageId, $properties->messageId());
        self::assertEquals($timestamp, $properties->timestamp());
        self::assertEquals($userId, $properties->userId());
        self::assertEquals($appId, $properties->appId());
        self::assertEquals($clusterId, $properties->clusterId());
        self::assertEquals($headers, $properties->headers());
    }

    public function testShouldTransformMessageWithoutProperties(): void
    {
        $body = 'Test message body';

        $message = $this->createMock(AMQPMessage::class);
        $message->method('getBody')->willReturn($body);
        $message->method('get_properties')->willReturn([]);

        $envelope = $this->getTransformer()->transformMessage($message);
        self::assertEquals('', $envelope->getCommandClass());
        self::assertEquals($body, $envelope->getBody());
    }

    public function testShouldTransformEnvelope(): void
    {
        $body = 'Test message body';
        $commandClass = 'TestCommand';

        $contentType = 'json';
        $contentEncoding = 'UTF-8';
        $deliveryMode = DeliveryMode::Persistent;
        $priority = 1000;
        $correlationId = 'aassdd';
        $replyTo = 'test-reply';
        $expiration = 100;
        $messageId = 'test-message-id';
        $timestamp = 1234567890;
        $userId = '1234567';
        $appId = '4567';
        $clusterId = 'test-cluster-id';
        $headers = new AMQPTable([
            'first-header' => 'first-header-value',
            'second-header' => 'second-header-value',
        ]);

        $properties = CommandProperties::builder()
            ->contentType($contentType)
            ->contentEncoding($contentEncoding)
            ->deliveryMode($deliveryMode)
            ->priority($priority)
            ->correlationId($correlationId)
            ->replyTo($replyTo)
            ->expiration($expiration)
            ->messageId($messageId)
            ->timestamp($timestamp)
            ->userId($userId)
            ->clusterId($clusterId)
            ->appId($appId)
            ->addHeader('first-header', 'first-header-value')
            ->addHeader('second-header', 'second-header-value')
            ->build();

        $envelope = new MessageEnvelope($body, $commandClass, $properties);
        $message = $this->getTransformer()->transformEnvelope($envelope);
        $properties = $message->get_properties();

        self::assertEquals($body, $message->getBody());
        self::assertEquals($commandClass, $properties['type']);
        self::assertEquals($contentType, $properties['content_type']);
        self::assertEquals($contentEncoding, $properties['content_encoding']);
        self::assertEquals($deliveryMode->value, $properties['delivery_mode']);
        self::assertEquals($priority, $properties['priority']);
        self::assertEquals($correlationId, $properties['correlation_id']);
        self::assertEquals($replyTo, $properties['reply_to']);
        self::assertEquals($expiration, $properties['expiration']);
        self::assertEquals($messageId, $properties['message_id']);
        self::assertEquals($timestamp, $properties['timestamp']);
        self::assertEquals($userId, $properties['user_id']);
        self::assertEquals($appId, $properties['app_id']);
        self::assertEquals($clusterId, $properties['cluster_id']);
        self::assertEquals($headers, $properties['application_headers']);
    }

    public function testShouldTransformEnvelopeWithoutProperties(): void
    {
        $body = 'Test message body';
        $commandClass = 'TestCommand';

        $envelope = new MessageEnvelope($body, $commandClass);
        $message = $this->getTransformer()->transformEnvelope($envelope);
        $properties = $message->get_properties();

        self::assertEquals($body, $message->getBody());
        self::assertEquals($commandClass, $properties['type']);
    }

    private function getTransformer(): MessageTransformer
    {
        return new MessageTransformer();
    }
}
