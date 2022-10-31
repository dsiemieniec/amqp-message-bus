<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Handler;

use Siemieniec\AmqpMessageBus\Exception\HandlerDuplicateException;
use Siemieniec\AmqpMessageBus\Exception\HandlerMissingException;

final class HandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @var array<string, callable>
     */
    private array $registry = [];

    public function registerHandler(string $messageClass, callable $handler): void
    {
        if (isset($this->registry[$messageClass])) {
            throw new HandlerDuplicateException(
                \sprintf(
                    'Message %s already has handler %s',
                    $messageClass,
                    \get_debug_type($this->registry[$messageClass])
                )
            );
        }

        $this->registry[$messageClass] = $handler;
    }

    public function getHandler(object $message): callable
    {
        return $this->getHandlerByClass(\get_class($message));
    }

    public function getHandlerByClass(string $messageClass): callable
    {
        if (!isset($this->registry[$messageClass])) {
            throw new HandlerMissingException(\sprintf('Handler not registered for message %s', $messageClass));
        }

        return $this->registry[$messageClass];
    }
}
