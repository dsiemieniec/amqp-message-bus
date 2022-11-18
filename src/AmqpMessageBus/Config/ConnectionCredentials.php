<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

class ConnectionCredentials
{
    public const DEFAULT_VHOST = '/';

    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vHost = self::DEFAULT_VHOST,
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

    public function equals(ConnectionCredentials $credentials): bool
    {
        return $this->getHost() === $credentials->getHost()
            && $this->getUser() === $credentials->getUser()
            && $this->getPassword() === $credentials->getPassword()
            && $this->getPort() === $credentials->getPort()
            && $this->getVHost() === $credentials->getVHost();
    }
}
