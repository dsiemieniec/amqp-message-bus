<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\CommandBusException;
use App\Utils\Delay;

interface CommandBusInterface
{
    /**
     * @throws CommandBusException
     */
    public function execute(CommandInterface $command): void;

    /**
     * @throws CommandBusException
     */
    public function executeAsync(CommandInterface $command, ?Delay $delay = null): void;
}
