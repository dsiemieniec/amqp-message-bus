<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelope;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelopeInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractCommandSerializer implements CommandSerializerInterface
{
    protected SerializerInterface $serializer;

    abstract protected function init(): SerializerInterface;

    public function __construct()
    {
        $this->serializer = $this->init();
    }

    public function serialize(object $command, CommandProperties $properties): MessageEnvelopeInterface
    {
        return new MessageEnvelope(
            $this->serializer->serialize($command, 'json'),
            \get_class($command),
            $properties
        );
    }

    public function deserialize(MessageEnvelopeInterface $envelope): object
    {
        return $this->serializer->deserialize($envelope->getBody(), $envelope->getCommandClass(), 'json');
    }
}
