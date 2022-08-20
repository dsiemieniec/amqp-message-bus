<?php

namespace App\Handler;

use App\Command\SimpleCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SimpleCommandHandler
{
    public function __invoke(SimpleCommand $command): void
    {
        print_r([
            'id' => $command->getId(),
            'text' => $command->getText()
        ]);
    }
}
