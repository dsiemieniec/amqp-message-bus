<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Exception;

use RuntimeException;
use Siemieniec\AmqpMessageBus\Config\Exchange;
use Siemieniec\AmqpMessageBus\Config\QueueBinding;
use Throwable;

class BindingDeclarationException extends RuntimeException
{
    public function __construct(
        private Exchange $exchange,
        private QueueBinding $queueBinding,
        ?Throwable $previous = null
    ) {
        parent::__construct('Failed to declare binding', 0, $previous);
    }

    public function getExchange(): Exchange
    {
        return $this->exchange;
    }

    public function getQueueBinding(): QueueBinding
    {
        return $this->queueBinding;
    }
}
