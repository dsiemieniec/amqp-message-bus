<?php

namespace App\Config;

enum ExchangeType: string
{
    case DIRECT = 'direct';
    case TOPIC = 'topic';
    case FANOUT = 'fanout';
}