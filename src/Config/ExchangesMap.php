<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingExchangeException;

class ExchangesMap
{
    /** @var array<string, Exchange> */
    private array $exchanges;

    public function __construct()
    {
        $this->exchanges = [];
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->exchanges);
    }

    public function put(string $name, Exchange $exchange): void
    {
        $this->exchanges[$name] = $exchange;
    }

    public function get(string $name): Exchange
    {
        if (!$this->has($name)) {
            throw new MissingExchangeException(\sprintf('Exchange %s has not been defined', $name));
        }

        return $this->exchanges[$name];
    }

    /** @return Exchange[] */
    public function all(): array
    {
        return \array_values($this->exchanges);
    }
}
