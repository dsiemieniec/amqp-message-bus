<?php

declare(strict_types=1);

namespace App\Command\Properties\CommandProperty;

use App\Command\Properties\PropertyKey;

final class PriorityProperty extends AbstractIntegerValueCommandProperty
{
    public function getKey(): PropertyKey
    {
        return PropertyKey::Priority;
    }
}
