<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingMapItemException;

class BindingsMap
{
    /** @var array<string, Binding> */
    private array $bindings;

    public function __construct()
    {
        $this->bindings = [];
    }

    public function put(string $name, Binding $binding): void
    {
        $this->bindings[$name] = $binding;
    }

    public function get(string $name): Binding
    {
        if (!\array_key_exists($name, $this->bindings)) {
            throw new MissingMapItemException('Missing binding config with name ' . $name);
        }

        return $this->bindings[$name];
    }

    /** @return Binding[] */
    public function all(): array
    {
        return \array_values($this->bindings);
    }
}
