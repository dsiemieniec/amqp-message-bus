<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\SimpleCommand;
use Siemieniec\AmqpMessageBus\Attributes\AsMessageHandler;

#[AsMessageHandler]
final class SimpleCommandHandler
{
    public function __invoke(SimpleCommand $command): void
    {
        \print_r([
            'id' => $command->getId(),
            'text' => $command->getText()
        ]);
    }
}
