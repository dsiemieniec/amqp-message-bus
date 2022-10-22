<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Command\Properties;

use Siemieniec\AsyncCommandBus\Command\Properties\CommandPropertyInterface;
use Siemieniec\AsyncCommandBus\Command\Properties\PropertyKey;
use Siemieniec\AsyncCommandBus\Exception\InvalidArrayAccessException;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\TentativeType;

class StringProperty implements CommandPropertyInterface
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
