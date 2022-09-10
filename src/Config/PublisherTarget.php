<?php

namespace App\Config;

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
