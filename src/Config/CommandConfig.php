<?php

declare(strict_types=1);

namespace App\Config;

class CommandConfig
{
    public function __construct(
        private string $commandClass,
        private string $serializerClass,
        private CommandPublisherConfig $publisherConfig
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

    public function getPublisherConfig(): CommandPublisherConfig
    {
        return $this->publisherConfig;
    }
}
