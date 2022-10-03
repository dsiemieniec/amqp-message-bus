<?php

declare(strict_types=1);

namespace App\Config;

class Exchange
{
    public function __construct(
        private string $name,
        private ExchangeType $type,
        private Connection $connection,
        private bool $delayedActive = false
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ExchangeType
    {
        return $this->type;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function isDelayedActive(): bool
    {
        return $this->delayedActive;
    }
}
