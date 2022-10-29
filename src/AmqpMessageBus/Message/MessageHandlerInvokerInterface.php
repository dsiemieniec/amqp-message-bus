<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message;

interface MessageHandlerInvokerInterface
{
    public function handle(object $message): void;
}
