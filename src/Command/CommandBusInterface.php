<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Properties\CommandProperties;
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
    public function executeAsync(CommandInterface $command, ?CommandProperties $properties = null): void;
}
