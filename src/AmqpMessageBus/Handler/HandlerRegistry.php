<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Handler;

use Siemieniec\AmqpMessageBus\Exception\HandlerMissingException;

final class HandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @var array<string, callable[]>
     */
    private array $registry = [];

    public function registerHandler(string $messageClass, callable $handler): void
    {
        if (!isset($this->registry[$messageClass])) {
            $this->registry[$messageClass] = [];
        }

        $this->registry[$messageClass][] = $handler;
    }

    /**
     * @return callable[]
     */
    public function getHandlers(object $message): array
    {
        return $this->getHandlersByClass(\get_class($message));
    }

    /**
     * @return callable[]
     */
    public function getHandlersByClass(string $messageClass): array
    {
        if (!isset($this->registry[$messageClass])) {
            throw new HandlerMissingException(\sprintf('Handler not registered for message %s', $messageClass));
        }

        return $this->registry[$messageClass];
    }
}
