<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use Siemieniec\AsyncCommandBus\Rabbit\ConsumerCallbackInterface;
use Siemieniec\AsyncCommandBus\Config\Config;
use Siemieniec\AsyncCommandBus\Rabbit\CommandConsumer;

class CommandConsumerFactory
{
    public function __construct(
        private Config $config,
        private ConsumerCallbackInterface $callback
    ) {
    }

    public function create(string $name): CommandConsumer
    {
        return new CommandConsumer($this->config->getQueueConfig($name), $this->callback);
    }
}
