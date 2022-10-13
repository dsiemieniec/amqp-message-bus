<?php

declare(strict_types=1);

namespace App\Command\Properties;

interface CommandPropertyInterface
{
    public function getKey(): PropertyKey;
    public function getPropertyValueAsString(): string;
    public function equals(CommandPropertyInterface $property): bool;
}
