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

    /**
     * @param ConnectionCredentials[] $connectionCredentials
     */
    public function __construct(
        private string $name,
        private array $connectionCredentials,
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

    /**
     * @return ConnectionCredentials[]
     */
    public function getConnectionCredentials(): array
    {
        return $this->connectionCredentials;
    }

    public function getName(): string
    {
        return $this->name;
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
}
