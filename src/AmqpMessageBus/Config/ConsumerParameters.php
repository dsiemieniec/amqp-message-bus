<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

class ConsumerParameters
{
    public const NO_LIMIT = 0;
    public const DEFAULT_PREFETCH_COUNT = 1;
    public const DEFAULT_TAG = '';
    public const DEFAULT_ACK = true;
    public const DEFAULT_EXCLUSIVE = false;
    public const DEFAULT_LOCAL = true;

    public function __construct(
        private string $tag = self::DEFAULT_TAG,
        private bool $ack = self::DEFAULT_ACK,
        private bool $exclusive = self::DEFAULT_EXCLUSIVE,
        private bool $local = self::DEFAULT_LOCAL,
        private int $prefetchCount = self::DEFAULT_PREFETCH_COUNT,
        private int $timeLimit = self::NO_LIMIT,
        private int $waitTimeout = self::NO_LIMIT,
        private int $messagesLimit = self::NO_LIMIT
    ) {
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

    public function getPrefetchCount(): int
    {
        return $this->prefetchCount;
    }

    public function getTimeLimit(): int
    {
        return $this->timeLimit;
    }

    public function getWaitTimeout(): int
    {
        return $this->waitTimeout;
    }

    public function getMessagesLimit(): int
    {
        return $this->messagesLimit;
    }

    public function hasTimeLimit(): bool
    {
        return $this->getTimeLimit() > 0;
    }

    public function hasWaitTimeout(): bool
    {
        return $this->getWaitTimeout() > 0;
    }

    public function hasMessagesLimit(): bool
    {
        return $this->getMessagesLimit() > 0;
    }
}
