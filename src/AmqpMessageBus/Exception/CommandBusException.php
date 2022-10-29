<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Exception;

use RuntimeException;
use Throwable;

class CommandBusException extends RuntimeException
{
    public function __construct(
        string $message,
        private object $command,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getCommand(): object
    {
        return $this->command;
    }
}
