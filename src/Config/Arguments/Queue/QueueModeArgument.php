<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue;

use App\Config\Arguments\Queue\Enum\QueueArgumentKey;
use App\Config\Arguments\Queue\Enum\QueueModeType;

class QueueModeArgument extends StringQueueArgument
{
    public function __construct(QueueModeType $value)
    {
        parent::__construct(QueueArgumentKey::MODE, $value->value);
    }
}
