<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Siemieniec\AmqpMessageBus\Message\Properties\BasicHeader;
use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Message\Properties\PropertyKey;

final class MessageTransformer implements MessageTransformerInterface
{
    public function transformMessage(AMQPMessage $message): MessageEnvelopeInterface
    {
        $messageProperties = $message->get_properties();
        $messageClass = $messageProperties[PropertyKey::Type->value] ?? '';
        unset($messageProperties[PropertyKey::Type->value]);

        /** @var AMQPTable|null $headers */
        $headers = $messageProperties[PropertyKey::Headers->value] ?? null;
        $properties = new MessageProperties();
        foreach ($headers?->getNativeData() ?? [] as $key => $value) {
            $properties[PropertyKey::Headers] = new BasicHeader($key, $value);
        }
        unset($messageProperties[PropertyKey::Headers->value]);

        foreach ($messageProperties as $key => $value) {
            $properties[(string)$key] = $value;
        }

        return new MessageEnvelope(
            $message->getBody(),
            $messageClass,
            $properties
        );
    }

    public function transformEnvelope(MessageEnvelopeInterface $envelope): AMQPMessage
    {
        $properties = $envelope->getProperties();
        $headers = $properties->headers();
        $properties[PropertyKey::Type] = $envelope->getMessageClass();
        $properties = $properties->toArray();
        if (!empty($headers)) {
            $properties[PropertyKey::Headers->value] = new AMQPTable($headers);
        }

        return new AMQPMessage((string) $envelope->getBody(), $properties);
    }
}
