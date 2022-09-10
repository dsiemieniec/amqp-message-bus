<?php

namespace App\Rabbit;

use App\Config\Config;


class CommandConsumer implements CommandConsumerInterface
{
    public function __construct(
        private Config $config,
        private ConsumerCallbackInterface $callback
    ) {
    }

    public function consume(string $name, ConsumerLimits $limits): void
    {
        $queueConfig = $this->config->getQueueConfig($name);
        $connection = new RabbitConnection($queueConfig->getConnection());
        $connection->consume(new ConsumerParameters($queueConfig, $limits), $this->callback);
    }
}
