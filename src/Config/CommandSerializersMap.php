<?php

declare(strict_types=1);

namespace App\Config;

class CommandSerializersMap
{
    /** @var array<string, CommandSerializer> */
    private array $serializers = [];

    public function put(CommandSerializer $serializer): void
    {
        $this->serializers[$serializer->getCommandClass()] = $serializer;
    }

    public function get(string $commandClass): ?CommandSerializer
    {
        return $this->serializers[$commandClass] ?? null;
    }
}
