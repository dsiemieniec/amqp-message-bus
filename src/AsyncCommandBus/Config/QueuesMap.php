<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

final class QueuesMap extends AbstractMap
{

    public function current(): Queue
    {
        return parent::current();
    }

    /** @param string $offset */
    public function offsetGet(mixed $offset): Queue
    {
        return parent::offsetGet($offset);
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new \Siemieniec\AsyncCommandBus\Config\MissingQueueException(
            \sprintf('Queue %s has not been defined', $offset),
        );
    }

    protected function getValueType(): string
    {
        return Queue::class;
    }

}
