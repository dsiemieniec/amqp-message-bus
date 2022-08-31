<?php

namespace App\Handler;

use App\Command\AnotherSimpleCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AnotherSimpleCommandHandler
{
    public function __invoke(AnotherSimpleCommand $command): void
    {
        print_r(['text1' => $command->getFirstText(), 'text2' => $command->getSecondText()]);
    }
}
