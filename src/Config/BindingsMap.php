<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingBindingException;

class BindingsMap
{
    /** @var array<string, Binding> */
    private array $bindings;

    public function __construct()
    {
        $this->bindings = [];
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->bindings);
    }

    public function put(string $name, Binding $binding): void
    {
        $this->bindings[$name] = $binding;
    }

    public function get(string $name): Binding
    {
        if (!$this->has($name)) {
            throw new MissingBindingException(\sprintf('Binding %s has not been defined', $name));
        }

        return $this->bindings[$name];
    }

    /** @return Binding[] */
    public function all(): array
    {
        return \array_values($this->bindings);
    }
}
