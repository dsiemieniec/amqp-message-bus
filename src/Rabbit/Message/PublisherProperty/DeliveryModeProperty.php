<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

use App\Rabbit\Message\PublisherPropertyInterface;
use App\Rabbit\Message\PropertyKey;

final class DeliveryModeProperty implements PublisherPropertyInterface
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

    public function equals(PublisherPropertyInterface $property): bool
    {
        return $this->getKey()->equals($property->getKey())
            && $this->getPropertyValueAsString() === $property->getPropertyValueAsString();
    }
}
