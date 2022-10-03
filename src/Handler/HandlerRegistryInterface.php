<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\CommandInterface;

interface HandlerRegistryInterface
{
    public function getHandler(CommandInterface $command): HandlerInterface;
}
