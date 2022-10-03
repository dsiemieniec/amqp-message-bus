<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

use App\Rabbit\Message\PropertyKey;

final class MessageIdProperty extends AbstractStringValuePublisherProperty
{
    public function getKey(): PropertyKey
    {
        return PropertyKey::MessageId;
    }
}
