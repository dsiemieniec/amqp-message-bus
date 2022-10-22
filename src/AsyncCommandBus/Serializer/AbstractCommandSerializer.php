<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Serializer;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelope;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelopeInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractCommandSerializer implements CommandSerializerInterface
{
    protected SerializerInterface $serializer;

    abstract protected function init(): SerializerInterface;

    public function __construct()
    {
        $this->serializer = $this->init();
    }

    public function serialize(CommandInterface $command, CommandProperties $properties): MessageEnvelopeInterface
    {
        return new MessageEnvelope(
            $this->serializer->serialize($command, 'json'),
            $command::class,
            $properties
        );
    }

    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface
    {
        return $this->serializer->deserialize($envelope->getBody(), $envelope->getCommandClass(), 'json');
    }
}
