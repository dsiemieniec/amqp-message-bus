<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

final class ExchangesMap extends AbstractMap
{

    public function current(): Exchange
    {
        return parent::current();
    }

    /** @param string $offset */
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
        throw new \Siemieniec\AsyncCommandBus\Config\MissingExchangeException(
            \sprintf('Exchange %s has not been defined', $offset),
        );
    }

}
