<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Exception\MessageBusException;

interface MessageBusInterface
{
    /**
     * @throws MessageBusException
     */
    public function execute(object $message): void;

    /**
     * @throws MessageBusException
     */
    public function executeAsync(object $message, ?MessageProperties $properties = null): void;
}
