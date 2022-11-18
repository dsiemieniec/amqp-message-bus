<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Exception;

use RuntimeException;
use Siemieniec\AmqpMessageBus\Config\Queue;
use Throwable;

class QueueDeclarationException extends RuntimeException
{
    public function __construct(
        private Queue $queue,
        ?Throwable $previous = null
    ) {
        parent::__construct('Failed to declare queue', 0, $previous);
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}
