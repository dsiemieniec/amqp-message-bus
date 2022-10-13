<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingConnectionException;

class ConnectionsMap extends AbstractMap
{
    public function current(): Connection
    {
        return parent::current();
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): Connection
    {
        return parent::offsetGet($offset);
    }

    protected function getValueType(): string
    {
        return Connection::class;
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingConnectionException(\sprintf('Connection %s has not been defined', $offset));
    }
}
