<?php

namespace App\Command;

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
        $this->handlerRegistry->getHandler($command)($command);
    }

    public function executeAsync(CommandInterface $command, ?Delay $delay = null): void
    {
        $this->commandPublisher->publish($command, $delay);
    }
}
