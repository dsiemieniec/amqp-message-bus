<?php

declare(strict_types=1);

namespace App\Command\Properties\CommandProperty;

use App\Command\Properties\PropertyKey;
use App\Command\Properties\CommandPropertyInterface;

class Headers implements CommandPropertyInterface
{
    /**
     * @var array<string, HeaderInterface>
     */
    private array $headers = [];

    public function __construct(HeaderInterface ...$headers)
    {
        foreach ($headers as $header) {
            $this->add($header);
        }
    }

    public static function builder(): HeadersBuilder
    {
        return new HeadersBuilder();
    }

    public function isEmpty(): bool
    {
        return $this->getSize() === 0;
    }

    public function getSize(): int
    {
        return \count($this->headers);
    }

    public function getValue(string $name): ?string
    {
        return isset($this->headers[$name]) ? $this->headers[$name]->getValue() : null;
    }

    public function add(HeaderInterface $header): void
    {
        $this->headers[$header->getName()] = $header;
    }

    public function getKey(): PropertyKey
    {
        return PropertyKey::Headers;
    }

    /**
     * @return HeaderInterface[]
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * @return array<array<string, string>>
     */
    public function toArray(): array
    {
        return \array_map(fn(HeaderInterface $header): array => $header->jsonSerialize(), $this->headers);
    }

    public function getPropertyValueAsString(): string
    {
        return \json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public function equals(CommandPropertyInterface $property): bool
    {
        if (!($property instanceof Headers) || $this->getKey()->equals($property->getKey())) {
            return false;
        }

        if ($this->getSize() !== $property->getSize()) {
            return false;
        }

        foreach (\array_keys($this->headers) as $name) {
            if ($this->getValue($name) !== $property->getValue($name)) {
                return false;
            }
        }

        return true;
    }
}
