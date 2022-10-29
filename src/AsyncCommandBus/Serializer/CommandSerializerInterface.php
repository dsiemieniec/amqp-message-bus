<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Serializer;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelopeInterface;

interface CommandSerializerInterface
{
    public function serialize(CommandInterface $command, CommandProperties $properties): MessageEnvelopeInterface;

    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface;
}
