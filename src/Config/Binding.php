<?php

declare(strict_types=1);

namespace App\Config;

use InvalidArgumentException;

class Binding
{
    public function __construct(
        private Queue $queue,
        private Exchange $exchange,
        private string $routingKey = ''
    ) {
        if (!$exchange->getConnection()->equals($queue->getConnection())) {
            throw new InvalidArgumentException('Exchange and queue have different connection settings.');
        }
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function getExchange(): Exchange
    {
        return $this->exchange;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function getConnection(): Connection
    {
        return $this->queue->getConnection();
    }
}
