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
        $headers = [];
        foreach ($envelope->getProperties()->getHeaders()->all() as $header) {
            $headers[$header->getName()] = $header->getValue();
        }
        $properties['application_headers'] = new AMQPTable($headers);
        $properties[PropertyKey::Type->value] = $envelope->getCommandClass();

        return new AMQPMessage((string) $envelope->getBody(), $properties);
    }
}
