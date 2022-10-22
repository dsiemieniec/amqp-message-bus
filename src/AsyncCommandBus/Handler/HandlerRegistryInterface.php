<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Handler;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Handler\HandlerInterface;

interface HandlerRegistryInterface
{
    public function getHandler(CommandInterface $command): HandlerInterface;
}
