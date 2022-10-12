<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue\Enum;

enum QueueArgumentKey: string
{
    case AUTO_EXPIRE = 'x-expires';
    case MESSAGE_TTL = 'x-message-ttl';
    case OVERFLOW_BEHAVIOUR = 'x-overflow';
    case SINGLE_ACTIVE_CONSUMER = 'x-single-active-consumer';
    case DEAD_LETTER_EXCHANGE = 'x-dead-letter-exchange';
    case DEAD_LETTER_ROUTING_KEY = 'x-dead-letter-routing-key';
    case MAX_LENGTH = 'x-max-length';
    case MAX_LENGTH_BYTES = 'x-max-length-bytes';
    case MAXIMUM_PRIORITY = 'x-max-priority';
    case MODE = 'x-queue-mode';
    case VERSION = 'x-queue-version';
    case MASTER_LOCATOR = 'x-queue-master-locator';
}
