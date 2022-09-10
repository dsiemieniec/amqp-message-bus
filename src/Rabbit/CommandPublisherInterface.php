<?php

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Utils\Delay;

interface CommandPublisherInterface
{
    public function publish(CommandInterface $command, ?Delay $delay = null): void;
}
