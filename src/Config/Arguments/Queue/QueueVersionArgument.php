<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue;

use App\Config\Arguments\Queue\Enum\QueueArgumentKey;
use App\Config\Arguments\Queue\Enum\QueueVersionType;

class QueueVersionArgument extends IntegerQueueArgument
{
    public function __construct(QueueVersionType $value)
    {
        parent::__construct(QueueArgumentKey::VERSION, $value->value);
    }
}
