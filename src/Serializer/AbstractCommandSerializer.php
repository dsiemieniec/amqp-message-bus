<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Command\CommandInterface;
use App\Rabbit\Message\MessageEnvelope\MessageEnvelope;
use App\Rabbit\Message\MessageEnvelopeInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractCommandSerializer implements CommandSerializerInterface
{
    protected SerializerInterface $serializer;

    public function __construct()
    {
        $this->serializer = $this->init();
    }

    abstract protected function init(): SerializerInterface;

    public function serialize(CommandInterface $command): MessageEnvelopeInterface
    {
        return MessageEnvelope::builder(
            $this->serializer->serialize($command, 'json'),
            \get_class($command)
        )->build();
    }

    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface
    {
        return $this->serializer->deserialize($envelope->getBody(), $envelope->getCommandClass(), 'json');
    }
}
