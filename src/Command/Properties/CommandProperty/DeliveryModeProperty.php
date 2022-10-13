<?php

declare(strict_types=1);

namespace App\Command\Properties\CommandProperty;

use App\Command\Properties\CommandPropertyInterface;
use App\Command\Properties\PropertyKey;

final class DeliveryModeProperty implements CommandPropertyInterface
{
    public function __construct(
        private DeliveryMode $deliveryMode
    ) {
    }

    public function getKey(): PropertyKey
    {
        return PropertyKey::DeliveryMode;
    }

    public function getValue(): DeliveryMode
    {
        return $this->deliveryMode;
    }

    public function getPropertyValueAsString(): string
    {
        return (string)$this->deliveryMode->value;
    }

    public function equals(CommandPropertyInterface $property): bool
    {
        return $this->getKey()->equals($property->getKey())
            && $this->getPropertyValueAsString() === $property->getPropertyValueAsString();
    }
}
