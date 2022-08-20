<?php

namespace App\Command;

use App\Rabbit\CommandPublisherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandBus implements CommandBusInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private CommandPublisherInterface $commandPublisher
    ) {
    }

    public function execute(CommandInterface $command)
    {
        $this->messageBus->dispatch($command);
    }

    public function executeAsync(CommandInterface $command)
    {
        $this->commandPublisher->publish($command);
    }
}
