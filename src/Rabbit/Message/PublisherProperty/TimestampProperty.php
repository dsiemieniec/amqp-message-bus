<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

use App\Rabbit\Message\PropertyKey;

final class TimestampProperty extends AbstractIntegerValuePublisherProperty
{
    public function getKey(): PropertyKey
    {
        return PropertyKey::Timestamp;
    }
}
