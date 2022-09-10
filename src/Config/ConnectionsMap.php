<?php

namespace App\Config;

use App\Exception\MissingMapItemException;

class ConnectionsMap
{
    private array $connections;

    public function __construct()
    {
        $this->connections = [];
    }

    public function put(string $name, Connection $connection): void
    {
        $this->connections[$name] = $connection;
    }

    public function get(string $name): Connection
    {
        if (!\array_key_exists($name, $this->connections)) {
            throw new MissingMapItemException('Missing connection config with name ' . $name);
        }

        return $this->connections[$name];
    }
}