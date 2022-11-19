<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message\Properties;

use Siemieniec\AmqpMessageBus\Exception\InvalidArrayAccessException;

class StringProperty implements MessagePropertyInterface
{
    public function __construct(
        private PropertyKey $key,
        private string $value
    ) {
    }

    public function getKey(): PropertyKey
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function offsetExists(mixed $offset): bool
    {
        throw new InvalidArrayAccessException();
    }

    public function offsetGet(mixed $offset): mixed
    {
        throw new InvalidArrayAccessException();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new InvalidArrayAccessException();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new InvalidArrayAccessException();
    }
}
