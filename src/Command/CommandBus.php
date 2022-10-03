<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\CommandBusException;
use App\Handler\HandlerRegistryInterface;
use App\Rabbit\CommandPublisherInterface;
use App\Utils\Delay;

class CommandBus implements CommandBusInterface
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
            throw new CommandBusException(\sprintf('%s is not callable', \get_class($handler)));
        }

        $handler($command);
    }

    public function executeAsync(CommandInterface $command, ?Delay $delay = null): void
    {
        $this->commandPublisher->publish($command, $delay);
    }
}
