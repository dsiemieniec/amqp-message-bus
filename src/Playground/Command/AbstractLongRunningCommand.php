<?php

declare(strict_types=1);

namespace App\Command;

abstract class AbstractLongRunningCommand
{
    protected int $executionTime = 0;

    public function getExecutionTime(): int
    {
        return $this->executionTime;
    }

    public function setExecutionTime(int $executionTime): static
    {
        $this->executionTime = $executionTime;

        return $this;
    }
}
