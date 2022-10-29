<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Command;

use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Exception\CommandBusException;

interface CommandBusInterface
{
    /**
     * @throws CommandBusException
     */
    public function execute(object $command): void;

    /**
     * @throws CommandBusException
     */
    public function executeAsync(object $command, ?CommandProperties $properties = null): void;
}
