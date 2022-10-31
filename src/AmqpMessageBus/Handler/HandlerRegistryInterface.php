<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Handler;

interface HandlerRegistryInterface
{
    /**
     * @return callable[]
     */
    public function getHandlers(object $message): array;
}
