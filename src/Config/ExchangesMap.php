<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingExchangeException;

class ExchangesMap extends AbstractMap
{
    public function current(): Exchange
    {
        return parent::current();
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): Exchange
    {
        return parent::offsetGet($offset);
    }

    protected function getValueType(): string
    {
        return Exchange::class;
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingExchangeException(\sprintf('Exchange %s has not been defined', $offset));
    }
}
