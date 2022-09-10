<?php

namespace App\Config;

use App\Exception\MissingMapItemException;

class ExchangesMap
{
    /** @var array<string, Exchange> */
    private array $exchanges;

    public function __construct()
    {
        $this->exchanges = [];
    }

    public function put(string $name, Exchange $exchange): void
    {
        $this->exchanges[$name] = $exchange;
    }

    public function get(string $name): Exchange
    {
        if (!\array_key_exists($name, $this->exchanges)) {
            throw new MissingMapItemException('Missing exchange config with name ' . $name);
        }

        return $this->exchanges[$name];
    }

    /** @return Exchange[] */
    public function all(): array
    {
        return \array_values($this->exchanges);
    }
}
