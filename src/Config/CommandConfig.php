<?php

declare(strict_types=1);

namespace App\Config;

class CommandConfig
{
    public const DEFAULT_REQUEUE_ON_FAILURE = false;

    public function __construct(
        private string $commandClass,
        private string $serializerClass,
        private CommandPublisherConfig $publisherConfig,
        private bool $requeueOnFailure = self::DEFAULT_REQUEUE_ON_FAILURE
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

    public function requeueOnFailure(): bool
    {
        return $this->requeueOnFailure;
    }
}
