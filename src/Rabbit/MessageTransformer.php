<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\Properties\PropertyKey;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

final class MessageTransformer implements MessageTransformerInterface
{
    public function transformMessage(AMQPMessage $message): MessageEnvelopeInterface
    {
        $properties = $message->get_properties();

        return new MessageEnvelope(
            $message->getBody(),
            $properties[PropertyKey::Type->value] ?? ''
        );
    }

    public function transformEnvelope(MessageEnvelopeInterface $envelope): AMQPMessage
    {
        $properties = [];

        return new AMQPMessage((string) $envelope->getBody(), $properties);
    }
}
