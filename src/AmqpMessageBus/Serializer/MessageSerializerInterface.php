<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelopeInterface;

interface MessageSerializerInterface
{
    public function serialize(object $message, MessageProperties $properties): MessageEnvelopeInterface;

    public function deserialize(MessageEnvelopeInterface $envelope): object;
}
