<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Config\CommandPublisherConfig;
use Siemieniec\AsyncCommandBus\Config\Connection;
use Siemieniec\AsyncCommandBus\Config\PublisherTarget;
use Siemieniec\AsyncCommandBus\Config\Queue;

final class QueuePublishedCommandConfig implements CommandPublisherConfig
{
    public function __construct(
        private Queue $queue
    ) {
    }

    public function getPublisherTarget(): PublisherTarget
    {
        return new PublisherTarget($this->queue->getName());
    }

    public function getConnection(): Connection
    {
        return $this->queue->getConnection();
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}
