<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingQueueException;
use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

class QueuesMap implements ArrayAccess, Countable, Iterator
{
    /** @var array<string, Queue> */
    private array $queues = [];
    /** @var array<string> */
    private array $keys = [];
    private int $position = 0;

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        $this->assertOffsetType($offset);

        return \array_key_exists($offset, $this->queues);
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): Queue
    {
        $this->assertOffsetType($offset);

        return $this->queues[$offset]
            ?? throw new MissingQueueException(\sprintf('Queue %s has not been defined', $offset));
    }

    /**
     * @param string $offset
     * @param Queue $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertOffsetType($offset);
        $this->assertValueType($value);

        $this->queues[$offset] = $value;
        if (!\in_array($offset, $this->keys)) {
            $this->keys[] = $offset;
        }
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->assertOffsetType($offset);

        unset($this->queues[$offset]);
        $key = \array_search($offset, $this->keys);
        if ($key !== false) {
            unset($this->keys[$key]);
        }
    }

    public function count(): int
    {
        return \count($this->queues);
    }

    public function current(): Queue
    {
        return $this->queues[$this->keys[$this->position]];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): string
    {
        return $this->keys[$this->position];
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    private function assertOffsetType(mixed $offset): void
    {
        if (!\is_string($offset)) {
            throw new InvalidArgumentException(
                \sprintf('Offset must be of type string. %s given', \get_debug_type($offset))
            );
        }
    }

    private function assertValueType(mixed $value): void
    {
        if (!($value instanceof Queue)) {
            throw new InvalidArgumentException(
                \sprintf('Value must be of type %s. %s given', Queue::class, \get_debug_type($value))
            );
        }
    }
}
