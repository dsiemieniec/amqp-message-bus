<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingBindingException;
use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

class BindingsMap implements ArrayAccess, Countable, Iterator
{
    /** @var array<string, Binding> */
    private array $bindings = [];
    /** @var string[] */
    private array $keys = [];
    private int $position = 0;

    public function current(): Binding
    {
        return $this->bindings[$this->keys[$this->position]];
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

    public function count(): int
    {
        return \count($this->bindings);
    }

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        $this->assertOffsetType($offset);

        return \array_key_exists($offset, $this->bindings);
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): Binding
    {
        $this->assertOffsetType($offset);

        return $this->bindings[$offset]
            ?? throw new MissingBindingException(\sprintf('Queue %s has not been defined', $offset));
    }

    /**
     * @param string $offset
     * @param Binding $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertOffsetType($offset);
        $this->assertValueType($value);

        $this->bindings[$offset] = $value;
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

        unset($this->bindings[$offset]);
        $key = \array_search($offset, $this->keys);
        if ($key !== false) {
            unset($this->keys[$key]);
        }
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
        if (!($value instanceof Binding)) {
            throw new InvalidArgumentException(
                \sprintf('Value must be of type %s. %s given', Binding::class, \get_debug_type($value))
            );
        }
    }
}
