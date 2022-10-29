<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

abstract class AbstractMap implements ArrayAccess, Countable, Iterator
{
    /** @var array<string, mixed> */
    protected array $items = [];

    /** @var array<string> */
    protected array $keys = [];
    protected int $position = 0;

    abstract protected function getValueType(): string;

    abstract protected function onMissingOffset(mixed $offset): mixed;

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        $this->assertOffsetType($offset);

        return $this->items[$offset]
            ?? $this->onMissingOffset($offset);
    }

    public function current(): mixed
    {
        return $this->items[$this->keys[$this->position]];
    }

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        $this->assertOffsetType($offset);

        return \array_key_exists($offset, $this->items);
    }

    /**
     * @param string $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertOffsetType($offset);
        $this->assertValueType($value);

        $this->items[$offset] = $value;
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

        unset($this->items[$offset]);
        $key = \array_search($offset, $this->keys);
        if ($key !== false) {
            unset($this->keys[$key]);
        }
    }

    public function count(): int
    {
        return \count($this->items);
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

    protected function assertOffsetType(mixed $offset): void
    {
        if (!\is_string($offset)) {
            throw new InvalidArgumentException(
                \sprintf('Offset must be of type string. %s given', \get_debug_type($offset))
            );
        }
    }

    protected function assertValueType(mixed $value): void
    {
        if (!\is_a($value, $this->getValueType())) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Value must be of type %s. %s given',
                    $this->getValueType(),
                    \get_debug_type($value)
                )
            );
        }
    }
}
