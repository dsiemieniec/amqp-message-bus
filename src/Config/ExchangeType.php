<?php

declare(strict_types=1);

namespace App\Config;

enum ExchangeType: string
{
    case DIRECT = 'direct';
    case TOPIC = 'topic';
    case FANOUT = 'fanout';
}
