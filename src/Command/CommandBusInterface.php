<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\Delay;

interface CommandBusInterface
{
    public function execute(CommandInterface $command): void;
    public function executeAsync(CommandInterface $command, ?Delay $delay = null): void;
}
