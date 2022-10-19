<?php

declare(strict_types=1);

namespace App\Config;

class QueueBinding
{
    public function __construct(
        private Queue $queue,
        private string $routingKey
    ) {
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }
}
