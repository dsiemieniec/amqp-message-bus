<?php

namespace App\Handler;

use App\Command\SimpleCommand;

final class SimpleCommandHandler implements HandlerInterface
{
    public function __invoke(SimpleCommand $command): void
    {
        print_r([
            'id' => $command->getId(),
            'text' => $command->getText()
        ]);
    }
}
