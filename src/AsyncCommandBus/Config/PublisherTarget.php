<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

class PublisherTarget
{
    public function __construct(
        private string $routingKey,
        private string $exchange = ''
    ) {
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }
}
