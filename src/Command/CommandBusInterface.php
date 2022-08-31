<?php

namespace App\Command;

interface CommandBusInterface
{
    public function execute(CommandInterface $command): void;
    public function executeAsync(CommandInterface $command): void;
}
