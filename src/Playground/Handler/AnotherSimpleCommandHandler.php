<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\AnotherSimpleCommand;
use Siemieniec\AmqpMessageBus\Handler\HandlerInterface;

final class AnotherSimpleCommandHandler implements HandlerInterface
{
    public function __invoke(AnotherSimpleCommand $command): void
    {
        \print_r([
            'text1' => $command->getFirstText(),
            'text2' => $command->getSecondText(),
            'dateTime' => $command->getDateTime()->format(DATE_ISO8601)
        ]);
    }
}
