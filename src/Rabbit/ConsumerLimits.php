<?php

declare(strict_types=1);

namespace App\Rabbit;

class ConsumerLimits
{
    public const NO_LIMIT = 0;

    public function __construct(
        private int $timeLimit = self::NO_LIMIT,
        private int $timeout = self::NO_LIMIT,
        private int $messagesLimit = self::NO_LIMIT
    ) {
    }

    public function getTimeLimit(): int
    {
        return $this->timeLimit;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getMessagesLimit(): int
    {
        return $this->messagesLimit;
    }

    public function hasTimeLimit(): bool
    {
        return $this->getTimeLimit() > 0;
    }

    public function hasTimeout(): bool
    {
        return $this->getTimeout() > 0;
    }

    public function hasMessagesLimit(): bool
    {
        return $this->getMessagesLimit() > 0;
    }
}
