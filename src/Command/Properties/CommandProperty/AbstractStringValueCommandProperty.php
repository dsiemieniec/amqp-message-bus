<?php

declare(strict_types=1);

namespace App\Command\Properties\CommandProperty;

use App\Command\Properties\CommandPropertyInterface;
use App\Command\Properties\PropertyKey;

abstract class AbstractStringValueCommandProperty implements CommandPropertyInterface
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

    public function equals(CommandPropertyInterface $property): bool
    {
        return $this->getKey()->equals($property->getKey())
            && $this->getPropertyValueAsString() === $property->getPropertyValueAsString();
    }
}
