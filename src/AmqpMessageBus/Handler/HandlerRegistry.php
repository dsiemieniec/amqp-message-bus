<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Handler;

use Siemieniec\AmqpMessageBus\Exception\HandlerDuplicateException;
use Siemieniec\AmqpMessageBus\Exception\HandlerMissingException;

final class HandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @var array<string, HandlerInterface>
     */
    private array $registry = [];

    public function registerHandler(string $messageClass, HandlerInterface $handler): void
    {
        if (isset($this->registry[$messageClass])) {
            throw new HandlerDuplicateException(
                \sprintf(
                    'Message %s already has handler %s',
                    $messageClass,
                    \get_class($this->registry[$messageClass])
                )
            );
        }

        $this->registry[$messageClass] = $handler;
    }

    public function getHandler(object $message): HandlerInterface
    {
        return $this->getHandlerByClass(\get_class($message));
    }

    public function getHandlerByClass(string $messageClass): HandlerInterface
    {
        if (!isset($this->registry[$messageClass])) {
            throw new HandlerMissingException(\sprintf('Handler not registered for message %s', $messageClass));
        }

        return $this->registry[$messageClass];
    }
}
