<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelopeInterface;

interface CommandSerializerInterface
{
    public function serialize(object $command, CommandProperties $properties): MessageEnvelopeInterface;

    public function deserialize(MessageEnvelopeInterface $envelope): object;
}
