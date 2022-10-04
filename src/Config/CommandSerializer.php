<?php

declare(strict_types=1);

namespace App\Config;

class CommandSerializer
{
    public function __construct(
        private string $commandClass,
        private string $serializerClass
    ) {
    }

    public function getCommandClass(): string
    {
        return $this->commandClass;
    }

    public function getSerializerClass(): string
    {
        return $this->serializerClass;
    }
}
