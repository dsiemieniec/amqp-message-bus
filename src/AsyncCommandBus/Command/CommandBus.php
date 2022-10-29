<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Command;

use Siemieniec\AsyncCommandBus\Command\CommandBusInterface;
use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Exception\CommandBusException;
use Siemieniec\AsyncCommandBus\Handler\HandlerRegistryInterface;
use Siemieniec\AsyncCommandBus\Rabbit\CommandPublisherInterface;
use Throwable;

final class CommandBus implements CommandBusInterface
{
    public function __construct(
        private HandlerRegistryInterface $handlerRegistry,
        private CommandPublisherInterface $commandPublisher
    ) {
    }

    public function execute(CommandInterface $command): void
    {
        $handler = $this->handlerRegistry->getHandler($command);
        if (!\is_callable($handler)) {
            throw new CommandBusException(\sprintf('%s is not callable', \get_class($handler)), $command);
        }

        try {
            $handler($command);
        } catch (Throwable $throwable) {
            throw new CommandBusException(
                \sprintf('%s failed to process command', \get_class($handler)),
                $command,
                $throwable
            );
        }
    }

    public function executeAsync(CommandInterface $command, ?CommandProperties $properties = null): void
    {
        try {
            $this->commandPublisher->publish($command, $properties);
        } catch (Throwable $throwable) {
            throw new CommandBusException('Failed to publish command', $command, $throwable);
        }
    }
}
