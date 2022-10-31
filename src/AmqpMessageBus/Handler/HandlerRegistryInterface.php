<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Handler;

interface HandlerRegistryInterface
{
    public function getHandler(object $message): callable;
}
