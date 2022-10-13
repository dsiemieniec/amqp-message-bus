<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingBindingException;
use InvalidArgumentException;

class BindingsMap extends AbstractMap
{
    public function current(): Binding
    {
        return parent::current();
    }

    public function offsetGet(mixed $offset): Binding
    {
        return parent::offsetGet($offset);
    }

    protected function assertValueType(mixed $value): void
    {
        if (!($value instanceof Binding)) {
            throw new InvalidArgumentException(
                \sprintf('Value must be of type %s. %s given', Binding::class, \get_debug_type($value))
            );
        }
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingBindingException(\sprintf('Binding %s has not been defined', $offset));
    }
}
