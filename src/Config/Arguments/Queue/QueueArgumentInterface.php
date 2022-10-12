<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue;

interface QueueArgumentInterface
{
    public function getKey(): string;
    public function getValue(): mixed;
}
