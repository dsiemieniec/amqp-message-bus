<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Command\CommandInterface;
use App\Command\Properties\CommandProperties;
use App\Rabbit\MessageEnvelopeInterface;

interface CommandSerializerInterface
{
    public function serialize(CommandInterface $command, CommandProperties $properties): MessageEnvelopeInterface;
    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface;
}
