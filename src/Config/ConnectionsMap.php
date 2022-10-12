<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingConnectionException;

class ConnectionsMap
{
    /** @var array<string, Connection> */
    private array $connections;

    public function __construct()
    {
        $this->connections = [];
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->connections);
    }

    public function put(Connection $connection): void
    {
        $this->connections[$connection->getName()] = $connection;
    }

    public function get(string $name): Connection
    {
        if (!$this->has($name)) {
            throw new MissingConnectionException(\sprintf('Connection %s has not been defined', $name));
        }

        return $this->connections[$name];
    }
}
