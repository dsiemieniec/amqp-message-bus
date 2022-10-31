<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Config\Config;

class MessageConsumerFactory
{
    public function __construct(
        private Config $config,
        private ConsumerCallbackInterface $callback
    ) {
    }

    public function create(string $name): MessageConsumer
    {
        return new MessageConsumer($this->config->getQueueConfig($name), $this->callback);
    }
}
