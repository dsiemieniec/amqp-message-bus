<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Config\Queue;

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
