<?php

declare(strict_types=1);

namespace App\Command\Properties;

use App\Exception\InvalidArrayAccessException;

final class DeliveryModeProperty implements CommandPropertyInterface
{
    private DeliveryMode $deliveryMode;

    public function __construct(DeliveryMode|int $deliveryMode)
    {
        $this->deliveryMode = $deliveryMode instanceof DeliveryMode ? $deliveryMode : DeliveryMode::from($deliveryMode);
    }

    public function getKey(): PropertyKey
    {
        return PropertyKey::DeliveryMode;
    }

    public function getValue(): int
    {
        return $this->deliveryMode->value;
    }

    public function offsetExists(mixed $offset): bool
    {
        throw new InvalidArrayAccessException();
    }

    public function offsetGet(mixed $offset): mixed
    {
        throw new InvalidArrayAccessException();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new InvalidArrayAccessException();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new InvalidArrayAccessException();
    }
}
