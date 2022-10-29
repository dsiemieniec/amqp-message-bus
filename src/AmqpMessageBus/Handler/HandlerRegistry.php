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

    public function registerHandler(string $commandClass, HandlerInterface $handler): void
    {
        if (isset($this->registry[$commandClass])) {
            throw new HandlerDuplicateException(
                \sprintf(
                    'Command %s already has handler %s',
                    $commandClass,
                    \get_class($this->registry[$commandClass])
                )
            );
        }

        $this->registry[$commandClass] = $handler;
    }

    public function getHandler(object $command): HandlerInterface
    {
        return $this->getHandlerByClass(\get_class($command));
    }

    public function getHandlerByClass(string $commandClass): HandlerInterface
    {
        if (!isset($this->registry[$commandClass])) {
            throw new HandlerMissingException(\sprintf('Handler not registered for command %s', $commandClass));
        }

        return $this->registry[$commandClass];
    }
}
