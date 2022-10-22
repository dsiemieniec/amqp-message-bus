<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Config\PublisherTarget;
use Siemieniec\AsyncCommandBus\Config\CommandPublisherConfig;
use Siemieniec\AsyncCommandBus\Config\Connection;
use Siemieniec\AsyncCommandBus\Config\Exchange;

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