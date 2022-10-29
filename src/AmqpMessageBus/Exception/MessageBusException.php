<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Exception;

use RuntimeException;
use Throwable;

class MessageBusException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
