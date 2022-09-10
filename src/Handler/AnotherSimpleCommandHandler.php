<?php

namespace App\Handler;

use App\Command\AnotherSimpleCommand;

final class AnotherSimpleCommandHandler implements HandlerInterface
{
    public function __invoke(AnotherSimpleCommand $command): void
    {
        print_r(['text1' => $command->getFirstText(), 'text2' => $command->getSecondText()]);
    }
}