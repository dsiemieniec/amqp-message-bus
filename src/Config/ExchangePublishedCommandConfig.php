<?php

namespace App\Config;

class ExchangePublishedCommandConfig implements CommandPublisherConfig
{
    public function __construct(
        private string $commandClass,
        private Exchange $exchange,
        private string $routingKey
    ) {
    }

    public function getCommandClass(): string
    {
        return $this->commandClass;
    }

    public function getPublisherTarget(): PublisherTarget
    {
        return new PublisherTarget($this->routingKey, $this->exchange->getName());
    }

    public function getConnection(): Connection
    {
        return $this->exchange->getConnection();
    }
}
