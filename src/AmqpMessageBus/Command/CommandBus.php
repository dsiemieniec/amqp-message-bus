<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Command;

use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;
use Siemieniec\AmqpMessageBus\Exception\CommandBusException;
use Siemieniec\AmqpMessageBus\Handler\HandlerRegistryInterface;
use Siemieniec\AmqpMessageBus\Rabbit\CommandPublisherInterface;
use Throwable;

final class CommandBus implements CommandBusInterface
{
    public function __construct(
        private HandlerRegistryInterface $handlerRegistry,
        private CommandPublisherInterface $commandPublisher
    ) {
    }

    public function execute(object $command): void
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

    public function executeAsync(object $command, ?CommandProperties $properties = null): void
    {
        try {
            $this->commandPublisher->publish($command, $properties);
        } catch (Throwable $throwable) {
            throw new CommandBusException('Failed to publish command', $command, $throwable);
        }
    }
}
