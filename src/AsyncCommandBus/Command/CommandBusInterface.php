<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Command;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;

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
