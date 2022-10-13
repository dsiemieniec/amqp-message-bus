<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Command\CommandInterface;
use App\Command\Properties\CommandProperties;
use App\Rabbit\MessageEnvelope;
use App\Rabbit\MessageEnvelopeInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractCommandSerializer implements CommandSerializerInterface
{
    protected SerializerInterface $serializer;

    public function __construct()
    {
        $this->serializer = $this->init();
    }

    abstract protected function init(): SerializerInterface;

    public function serialize(CommandInterface $command, CommandProperties $properties): MessageEnvelopeInterface
    {
        return new MessageEnvelope(
            $this->serializer->serialize($command, 'json'),
            \get_class($command),
            $properties
        );
    }

    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface
    {
        return $this->serializer->deserialize($envelope->getBody(), $envelope->getCommandClass(), 'json');
    }
}
