<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Properties\CommandProperties;
use App\Exception\CommandBusException;
use App\Handler\HandlerRegistryInterface;
use App\Rabbit\CommandPublisherInterface;
use App\Utils\Delay;
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
