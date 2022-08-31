<?php

namespace App\Handler;

use App\Command\DispatchedToOwnQueueCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DispatchedToOwnQueueCommandHandler
{
    public function __invoke(DispatchedToOwnQueueCommand $command): void
    {
        print_r([
            'id' => $command->getId(),
            'text' => $command->getText()
        ]);
    }
}
