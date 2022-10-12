<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue;

use App\Config\Arguments\Queue\Enum\OverflowBehaviourType;
use App\Config\Arguments\Queue\Enum\QueueArgumentKey;

class OverflowBehaviourArgument extends StringQueueArgument
{
    public function __construct(OverflowBehaviourType $value)
    {
        parent::__construct(QueueArgumentKey::OVERFLOW_BEHAVIOUR, $value->value);
    }
}
