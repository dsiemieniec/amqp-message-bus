<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Config\Config;

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
