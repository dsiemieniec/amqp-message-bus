<?php

namespace App\Config;

class Exchange
{
    public function __construct(
        private string $name,
        private ExchangeType $type,
        private Connection $connection
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
}
