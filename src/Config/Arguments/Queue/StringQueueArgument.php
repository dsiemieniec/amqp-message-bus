<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue;

use App\Config\Arguments\Queue\Enum\QueueArgumentKey;

class StringQueueArgument implements QueueArgumentInterface
{
    public function __construct(
        private QueueArgumentKey $key,
        private string $value
    ) {
    }

    public function getKey(): string
    {
        return $this->key->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
