<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Command\CommandInterface;
use App\Rabbit\Message\MessageEnvelopeInterface;

interface CommandSerializerInterface
{
    public function serialize(CommandInterface $command): MessageEnvelopeInterface;
    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface;
}
