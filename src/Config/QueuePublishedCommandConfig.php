<?php

namespace App\Config;

class QueuePublishedCommandConfig implements CommandPublisherConfig
{
    public function __construct(
        private string $commandClass,
        private Queue $queue
    ) {
    }

    public function getCommandClass(): string
    {
        return $this->commandClass;
    }

    public function getPublisherTarget(): PublisherTarget
    {
        return new PublisherTarget($this->queue->getName());
    }

    public function getConnection(): Connection
    {
        return $this->queue->getConnection();
    }
}
