<?php

declare(strict_types=1);

namespace App\Config;

class Queue
{
    public const DEFAULT_PASSIVE = false;
    public const DEFAULT_DURABLE = false;
    public const DEFAULT_EXCLUSIVE = false;
    public const DEFAULT_AUTO_DELETE = false;
    public const DEFAULT_AUTO_DECLARE = false;

    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        private string $name,
        private Connection $connection,
        private ConsumerParameters $consumerParameters,
        private bool $passive = self::DEFAULT_PASSIVE,
        private bool $durable = self::DEFAULT_DURABLE,
        private bool $exclusive = self::DEFAULT_EXCLUSIVE,
        private bool $autoDelete = self::DEFAULT_AUTO_DELETE,
        private bool $autoDeclare = self::DEFAULT_AUTO_DECLARE,
        private array $arguments = []
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

    /**
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function hasArguments(): bool
    {
        return !empty($this->arguments);
    }

    public function canAutoDeclare(): bool
    {
        return $this->autoDeclare;
    }
}
