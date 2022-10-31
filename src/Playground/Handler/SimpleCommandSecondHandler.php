<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\SimpleCommand;
use Siemieniec\AmqpMessageBus\Attributes\AsMessageHandler;

#[AsMessageHandler]
class SimpleCommandSecondHandler
{
    public function __invoke(SimpleCommand $simpleCommand): void
    {
        echo 'Second Handler for simple command' . PHP_EOL;
    }
}
