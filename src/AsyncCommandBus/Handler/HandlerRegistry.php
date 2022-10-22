<?php

declare(strict_types = 1);

namespace Siemieniec\AsyncCommandBus\Handler;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Handler\HandlerDuplicateException;
use Siemieniec\AsyncCommandBus\Handler\HandlerMissingException;
use function get_class;
use function sprintf;

final class HandlerRegistry implements HandlerRegistryInterface
{
    /** @var array<string, \Siemieniec\AsyncCommandBus\Handler\HandlerInterface> */
    private array $registry = [];

    public function registerHandler(string $commandClass, HandlerInterface $handler): void
    {
        if (isset($this->registry[$commandClass])) {
            throw new \Siemieniec\AsyncCommandBus\Handler\HandlerDuplicateException(
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
        return $this->getHandlerByClass($command::class);
    }

    public function getHandlerByClass(string $commandClass): HandlerInterface
    {
        if (!isset($this->registry[$commandClass])) {
            throw new \Siemieniec\AsyncCommandBus\Handler\HandlerMissingException(
                \sprintf('Handler not registered for command %s', $commandClass),
            );
        }

        return $this->registry[$commandClass];
    }
}
