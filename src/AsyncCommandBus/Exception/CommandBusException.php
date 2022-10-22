<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Exception;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use RuntimeException;
use Throwable;

class CommandBusException extends RuntimeException
{
    public function __construct(
        string $message,
        private CommandInterface $command,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
