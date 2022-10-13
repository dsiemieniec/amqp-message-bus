<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingQueueException;
use InvalidArgumentException;

class QueuesMap extends AbstractMap
{
    public function current(): Queue
    {
        return parent::current();
    }

    public function offsetGet(mixed $offset): Queue
    {
        return parent::offsetGet($offset);
    }

    protected function assertValueType(mixed $value): void
    {
        if (!($value instanceof Queue)) {
            throw new InvalidArgumentException(
                \sprintf('Value must be of type %s. %s given', Queue::class, \get_debug_type($value))
            );
        }
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingQueueException(\sprintf('Queue %s has not been defined', $offset));
    }
}
