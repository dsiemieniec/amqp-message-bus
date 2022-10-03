<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

enum DeliveryMode: int
{
    case NonPersistent = 1;
    case Persistent = 2;
}
