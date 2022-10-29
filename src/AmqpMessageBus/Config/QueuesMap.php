<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

use Siemieniec\AmqpMessageBus\Exception\MissingQueueException;

class QueuesMap extends AbstractMap
{
    public function current(): Queue
    {
        return parent::current();
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): Queue
    {
        return parent::offsetGet($offset);
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingQueueException(\sprintf('Queue %s has not been defined', $offset));
    }

    protected function getValueType(): string
    {
        return Queue::class;
    }
}
