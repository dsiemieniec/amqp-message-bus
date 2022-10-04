<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\DispatchedToOwnQueueCommand;
use App\Service\SomeTestService;

final class DispatchedToOwnQueueCommandHandler implements HandlerInterface
{
    public function __construct(
        private SomeTestService $service
    ) {
    }

    public function __invoke(DispatchedToOwnQueueCommand $command): void
    {
        print_r([
            'id' => $command->getId(),
            'text' => $command->getText()
        ]);

        $this->service->doSomething();
    }
}
