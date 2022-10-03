<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

use App\Rabbit\Message\PublisherPropertyInterface;
use App\Rabbit\Message\PropertyKey;

abstract class AbstractStringValuePublisherProperty implements PublisherPropertyInterface
{
    public function __construct(
        private string $value
    ) {
    }

    abstract public function getKey(): PropertyKey;

    public function getPropertyValueAsString(): string
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(PublisherPropertyInterface $property): bool
    {
        return $this->getKey()->equals($property->getKey())
            && $this->getPropertyValueAsString() === $property->getPropertyValueAsString();
    }
}
