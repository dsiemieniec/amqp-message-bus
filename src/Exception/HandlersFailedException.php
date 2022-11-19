<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Exception;

use RuntimeException;
use Throwable;

class HandlersFailedException extends RuntimeException
{
    /**
     * @param Throwable[] $exceptions
     */
    public function __construct(
        string $message = "",
        private array $exceptions = []
    ) {
        parent::__construct($message);
    }

    /**
     * @return Throwable[]
     */
    public function getNestedExceptions(): array
    {
        return $this->exceptions;
    }
}
