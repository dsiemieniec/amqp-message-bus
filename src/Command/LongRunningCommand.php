<?php

declare(strict_types=1);

namespace App\Command;

class LongRunningCommand implements CommandInterface
{
    public function __construct(
        private int $seconds,
    ) {
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }
}
