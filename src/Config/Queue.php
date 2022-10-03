<?php

namespace App\Config;

class Queue
{
    public function __construct(
        private string $name,
        private Connection $connection,
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
}
