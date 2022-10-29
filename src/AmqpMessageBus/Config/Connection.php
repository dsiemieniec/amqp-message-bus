<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

class Connection
{
    public const DEFAULT_CONNECTION_NAME = 'default';
    public const DEFAULT_VHOST = '/';

    public function __construct(
        private string $name,
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vHost = self::DEFAULT_VHOST
    ) {
    }

    public function getName(): string
    {
        return $this->name;
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
