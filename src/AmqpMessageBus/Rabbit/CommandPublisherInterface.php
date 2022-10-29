<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;

interface CommandPublisherInterface
{
    public function publish(object $command, ?CommandProperties $commandProperties = null): void;
}
