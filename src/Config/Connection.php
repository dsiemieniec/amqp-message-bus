<?php

namespace App\Config;

class Connection
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vHost = '/'
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getVHost(): string
    {
        return $this->vHost;
    }

    public function equals(Connection $connection): bool
    {
        return $this->getHost() === $connection->getHost()
            && $this->getUser() === $connection->getUser()
            && $this->getPassword() === $connection->getPassword()
            && $this->getPort() === $connection->getPort()
            && $this->getVHost() === $connection->getVHost();
    }
}
