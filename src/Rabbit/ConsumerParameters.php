<?php

namespace App\Rabbit;

use App\Config\Queue;

class ConsumerParameters
{
    public function __construct(
        private Queue $queue,
        private ConsumerLimits $limits,
        private string $tag = '',
        private bool $ack = true,
        private bool $exclusive = false,
        private bool $local = true,
    ) {
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function isAck(): bool
    {
        return $this->ack;
    }

    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function getLimits(): ConsumerLimits
    {
        return $this->limits;
    }
}
