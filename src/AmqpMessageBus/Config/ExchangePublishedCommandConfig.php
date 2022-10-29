<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

class ExchangePublishedCommandConfig implements CommandPublisherConfig
{
    public function __construct(
        private Exchange $exchange,
        private string $routingKey
    ) {
    }

    public function getPublisherTarget(): PublisherTarget
    {
        return new PublisherTarget($this->routingKey, $this->exchange->getName());
    }

    public function getConnection(): Connection
    {
        return $this->exchange->getConnection();
    }

    public function getExchange(): Exchange
    {
        return $this->exchange;
    }
}
