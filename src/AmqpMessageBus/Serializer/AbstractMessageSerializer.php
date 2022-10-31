<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelope;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelopeInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractMessageSerializer implements MessageSerializerInterface
{
    protected SerializerInterface $serializer;

    abstract protected function init(): SerializerInterface;

    public function __construct()
    {
        $this->serializer = $this->init();
    }

    public function serialize(object $message, MessageProperties $properties): MessageEnvelopeInterface
    {
        return new MessageEnvelope(
            $this->serializer->serialize($message, 'json'),
            \get_class($message),
            $properties
        );
    }

    public function deserialize(MessageEnvelopeInterface $envelope): object
    {
        return $this->serializer->deserialize($envelope->getBody(), $envelope->getMessageClass(), 'json');
    }
}
