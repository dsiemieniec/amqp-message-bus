<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandInterface;
use App\Command\Properties\CommandProperties;
use App\Utils\Delay;

interface CommandPublisherInterface
{
    public function publish(CommandInterface $command, ?CommandProperties $commandProperties = null): void;
}
