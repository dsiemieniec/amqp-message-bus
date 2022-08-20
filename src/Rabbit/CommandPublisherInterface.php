<?php

namespace App\Rabbit;

use App\Command\CommandInterface;

interface CommandPublisherInterface
{
    public function publish(CommandInterface $command): void;
}
