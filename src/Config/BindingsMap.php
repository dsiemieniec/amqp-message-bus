<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingBindingException;

class BindingsMap extends AbstractMap
{
    public function current(): Binding
    {
        return parent::current();
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): Binding
    {
        return parent::offsetGet($offset);
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingBindingException(\sprintf('Binding %s has not been defined', $offset));
    }

    protected function getValueType(): string
    {
        return Binding::class;
    }
}
