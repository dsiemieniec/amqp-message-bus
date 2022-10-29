<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Command;

use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;
use Siemieniec\AmqpMessageBus\Exception\CommandBusException;

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
