<?php

declare(strict_types=1);

namespace App\Config;

use App\Config\Arguments\Queue\QueueArgumentsCollection;

class Queue
{
    public const DEFAULT_PASSIVE = false;
    public const DEFAULT_DURABLE = false;
    public const DEFAULT_EXCLUSIVE = false;
    public const DEFAULT_AUTO_DELETE = false;

    public function __construct(
        private string $name,
        private Connection $connection,
        private ConsumerParameters $consumerParameters,
        private QueueArgumentsCollection $arguments,
        private bool $passive = false,
        private bool $durable = false,
        private bool $exclusive = false,
        private bool $autoDelete = false
    ) {
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isPassive(): bool
    {
        return $this->passive;
    }

    public function isDurable(): bool
    {
        return $this->durable;
    }

    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    public function isAutoDelete(): bool
    {
        return $this->autoDelete;
    }

    public function getConsumerParameters(): ConsumerParameters
    {
        return $this->consumerParameters;
    }

    public function getArguments(): QueueArgumentsCollection
    {
        return $this->arguments;
    }
}
