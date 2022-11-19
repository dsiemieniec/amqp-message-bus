<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Exception;

use RuntimeException;
use Siemieniec\AmqpMessageBus\Config\Exchange;
use Throwable;

class ExchangeDeclarationException extends RuntimeException
{
    public function __construct(private Exchange $exchange, ?Throwable $previous = null)
    {
        parent::__construct('Failed to declare exchange', 0, $previous);
    }

    public function getExchange(): Exchange
    {
        return $this->exchange;
    }
}
