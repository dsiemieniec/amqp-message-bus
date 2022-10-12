<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue\Enum;

enum QueueModeType: string
{
    case DEFAULT = 'default';
    case LAZY = 'lazy';
}
