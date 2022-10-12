<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

class QueueArgumentsCollection implements ArrayAccess, Iterator, Countable
{
    private int $position = 0;

    /**
     * @var QueueArgumentInterface[]
     */
    private array $arguments;

    /**
     * @var int[]|string[]
     */
    private array $keys;

    public function __construct(QueueArgumentInterface ...$arguments)
    {
        $this->arguments = $arguments;
        $this->keys = \array_keys($this->arguments);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->arguments[$offset]);
    }

    public function offsetGet(mixed $offset): ?QueueArgumentInterface
    {
        return $this->arguments[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!($value instanceof QueueArgumentInterface)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Value must be instance of %s. %s provided',
                    QueueArgumentInterface::class,
                    \get_class($value)
                )
            );
        }

        if ($offset === null) {
            $this->arguments[] = $value;
            $this->keys[] = \array_key_last($this->arguments);
        } else {
            $this->arguments[$offset] = $value;
            if (!\in_array($offset, $this->keys)) {
                $this->keys[] = $offset;
            }
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->arguments[$offset]);
    }

    public function current(): QueueArgumentInterface
    {
        return $this->arguments[$this->keys[$this->position]];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): string|int
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
        return \count($this->arguments);
    }
}
