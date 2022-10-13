<?php

declare(strict_types=1);

namespace App\Command\Properties;

enum DeliveryMode: int
{
    case NonPersistent = 1;
    case Persistent = 2;
}
