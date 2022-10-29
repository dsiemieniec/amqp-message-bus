<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;

interface CommandPublisherInterface
{
    public function publish(object $command, ?CommandProperties $commandProperties = null): void;
}
