<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue\Enum;

enum QueueVersionType: int
{
    case V1 = 1;
    case V2 = 2;
}
