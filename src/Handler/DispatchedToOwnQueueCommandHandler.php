<?php

namespace App\Handler;

use App\Command\DispatchedToOwnQueueCommand;

final class DispatchedToOwnQueueCommandHandler implements HandlerInterface
{
    public function __invoke(DispatchedToOwnQueueCommand $command): void
    {
        print_r([
            'id' => $command->getId(),
            'text' => $command->getText()
        ]);
    }
}
