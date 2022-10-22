<?php

declare(strict_types=1);

namespace App\Command;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;

class DispatchedToOwnQueueCommand implements CommandInterface
{
    public function __construct(
        private int $id,
        private string $text
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
