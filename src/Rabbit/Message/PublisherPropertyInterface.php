<?php

declare(strict_types=1);

namespace App\Rabbit\Message;

interface PublisherPropertyInterface
{
    public function getKey(): PropertyKey;
    public function getPropertyValueAsString(): string;
    public function equals(PublisherPropertyInterface $property): bool;
}
