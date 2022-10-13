<?php

declare(strict_types=1);

namespace App\Command\Properties\CommandProperty;

enum DeliveryMode: int
{
    case NonPersistent = 1;
    case Persistent = 2;
}
