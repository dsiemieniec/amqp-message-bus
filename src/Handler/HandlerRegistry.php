<?php

declare(strict_types=1);

namespace App\Handler;

use App\Command\CommandInterface;
use App\Exception\HandlerDuplicateException;
use App\Exception\HandlerMissingException;

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

    public function getHandler(CommandInterface $command): HandlerInterface
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
