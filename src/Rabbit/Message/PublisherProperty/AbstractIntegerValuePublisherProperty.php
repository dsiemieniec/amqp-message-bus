<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

use App\Rabbit\Message\PublisherPropertyInterface;
use App\Rabbit\Message\PropertyKey;

abstract class AbstractIntegerValuePublisherProperty implements PublisherPropertyInterface
{
    public function __construct(
        private int $value
    ) {
    }

    abstract public function getKey(): PropertyKey;

    public function getPropertyValueAsString(): string
    {
        return (string)$this->getValue();
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(PublisherPropertyInterface $property): bool
    {
        return $this->getKey()->equals($property->getKey())
            && $this->getPropertyValueAsString() === $property->getPropertyValueAsString();
    }
}
