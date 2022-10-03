<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

use App\Rabbit\Message\PropertyKey;

final class PriorityProperty extends AbstractIntegerValuePublisherProperty
{
    public function getKey(): PropertyKey
    {
        return PropertyKey::Priority;
    }
}
