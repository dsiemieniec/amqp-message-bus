<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use App\Utils\Delay;

interface CommandPublisherInterface
{
    public function publish(CommandInterface $command, ?CommandProperties $commandProperties = null): void;
}
