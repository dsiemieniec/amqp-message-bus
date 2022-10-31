<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

class Connection
{
    public const DEFAULT_CONNECTION_NAME = 'default';
    public const DEFAULT_VHOST = '/';
    public const DEFAULT_INSIST = false;
    public const DEFAULT_LOGIN_METHOD = 'AMQPLAIN';
    public const DEFAULT_LOCALE = 'en_US';
    public const DEFAULT_CONNECTION_TIMEOUT = 3.0;
    public const DEFAULT_READ_WRITE_TIMEOUT = 3.0;
    public const DEFAULT_KEEP_ALIVE = false;
    public const DEFAULT_HEARTBEAT = 0;
    public const DEFAULT_SSL_PROTOCOL = null;

    public function __construct(
        private string $name,
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vHost = self::DEFAULT_VHOST,
        private bool $insist = self::DEFAULT_INSIST,
        private string $loginMethod = self::DEFAULT_LOGIN_METHOD,
        private string $locale = self::DEFAULT_LOCALE,
        private float $connectionTimeout = self::DEFAULT_CONNECTION_TIMEOUT,
        private float $readWriteTimeout = self::DEFAULT_READ_WRITE_TIMEOUT,
        private bool $keepAlive = self::DEFAULT_KEEP_ALIVE,
        private int $heartbeat = self::DEFAULT_HEARTBEAT,
        private ?string $sslProtocol = self::DEFAULT_SSL_PROTOCOL
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

    public function isInsist(): bool
    {
        return $this->insist;
    }

    public function getLoginMethod(): string
    {
        return $this->loginMethod;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getConnectionTimeout(): float
    {
        return $this->connectionTimeout;
    }

    public function getReadWriteTimeout(): float
    {
        return $this->readWriteTimeout;
    }

    public function isKeepAlive(): bool
    {
        return $this->keepAlive;
    }

    public function getHeartbeat(): int
    {
        return $this->heartbeat;
    }

    public function getSslProtocol(): ?string
    {
        return $this->sslProtocol;
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
