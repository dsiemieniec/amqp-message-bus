<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue;

use App\Config\Arguments\Queue\Enum\QueueArgumentKey;

class BooleanQueueArgument implements QueueArgumentInterface
{
    public function __construct(
        private QueueArgumentKey $key,
        private bool $value
    ) {
    }

    public function getKey(): string
    {
        return $this->key->value;
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}
