<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message\Properties;

use InvalidArgumentException;

class Headers implements MessagePropertyInterface
{
    /**
     * @var array<string, HeaderInterface>
     */
    private array $headers = [];

    public function __construct(HeaderInterface ...$headers)
    {
        foreach ($headers as $header) {
            $this[] = $header;
        }
    }

    public function getKey(): PropertyKey
    {
        return PropertyKey::Headers;
    }

    /**
     * @return array<string, string>
     */
    public function getValue(): array
    {
        $headers = [];
        foreach ($this->headers as $header) {
            $headers[$header->getName()] = $header->getValue();
        }

        return $headers;
    }

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        $this->assertOffsetType($offset);

        return \array_key_exists($offset, $this->headers);
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): ?HeaderInterface
    {
        $this->assertOffsetType($offset);

        return $this->headers[$offset] ?? null;
    }

    /**
     * @param string|null $offset
     * @param string|HeaderInterface $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            if (!($value instanceof HeaderInterface)) {
                throw new InvalidArgumentException('Invalid header provided');
            }

            $offset = $value->getName();
        }

        $this->assertOffsetType($offset);
        $this->assertValueType($value);

        $this->headers[$offset] = $value instanceof HeaderInterface ? $value : new BasicHeader($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->assertOffsetType($offset);
        unset($this->headers[$offset]);
    }

    private function assertOffsetType(mixed $offset): void
    {
        if (!\is_string($offset)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Invalid offset type. Expected string but %s given',
                    \get_debug_type($offset)
                )
            );
        }
    }

    private function assertValueType(mixed $value): void
    {
        if (!\is_string($value) && !($value instanceof HeaderInterface)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Invalid header value type. Expected string or %s but %s given',
                    HeaderInterface::class,
                    \get_debug_type($value)
                )
            );
        }
    }
}
