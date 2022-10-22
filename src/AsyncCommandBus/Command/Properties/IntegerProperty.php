<?php

declare(strict_types = 1);

namespace Siemieniec\AsyncCommandBus\Command\Properties;

use Siemieniec\AsyncCommandBus\Command\Properties\InvalidArrayAccessException;

final class IntegerProperty implements CommandPropertyInterface
{
    public function __construct(private PropertyKey $key, private int $value)
    {
    }

    public function getKey(): PropertyKey
    {
        return $this->key;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function offsetExists(mixed $offset): bool
    {
        throw new InvalidArrayAccessException;
    }

    public function offsetGet(mixed $offset): mixed
    {
        throw new \Siemieniec\AsyncCommandBus\Command\Properties\InvalidArrayAccessException;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \Siemieniec\AsyncCommandBus\Command\Properties\InvalidArrayAccessException;
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \Siemieniec\AsyncCommandBus\Command\Properties\InvalidArrayAccessException;
    }
}
