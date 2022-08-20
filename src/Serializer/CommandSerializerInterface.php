<?php

namespace App\Serializer;

use App\Command\CommandInterface;

interface CommandSerializerInterface
{
    public function serialize(CommandInterface $command): string;
    public function deserialize(string $serializedCommand): CommandInterface;
}
