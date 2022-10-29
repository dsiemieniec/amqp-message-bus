<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Config\Connection;

class Exchange
{
    public const TYPE_DIRECT = 'direct';

    public const DEFAULT_PASSIVE = false;
    public const DEFAULT_DURABLE = false;
    public const DEFAULT_AUTO_DELETE = false;
    public const DEFAULT_INTERNAL = false;
    public const DEFAULT_AUTO_DECLARE = false;

    /**
     * @param array<string, mixed> $arguments
     * @param QueueBinding[] $queueBindings
     */
    public function __construct(
        private string $name,
        private string $type,
        private Connection $connection,
        private bool $passive = self::DEFAULT_PASSIVE,
        private bool $durable = self::DEFAULT_DURABLE,
        private bool $autoDelete = self::DEFAULT_AUTO_DELETE,
        private bool $internal = self::DEFAULT_INTERNAL,
        private bool $autoDeclare = self::DEFAULT_AUTO_DECLARE,
        private array $arguments = [],
        private array $queueBindings = []
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
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

    public function isPassive(): bool
    {
        return $this->passive;
    }

    public function isDurable(): bool
    {
        return $this->durable;
    }

    public function isAutoDelete(): bool
    {
        return $this->autoDelete;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    /**
     * @return QueueBinding[]
     */
    public function getQueueBindings(): array
    {
        return $this->queueBindings;
    }

    public function canAutoDeclare(): bool
    {
        return $this->autoDeclare;
    }
}
