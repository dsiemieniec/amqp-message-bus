<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Config\Queue;

class CommandConsumer implements CommandConsumerInterface
{
    private RabbitConnection $connection;

    public function __construct(
        private Queue $queueConfig,
        private ConsumerCallbackInterface $callback
    ) {
        $this->connection = new RabbitConnection($queueConfig->getConnection());
    }

    public function consume(): void
    {
        $this->connection->consume($this->queueConfig, $this->callback);
    }

    public function stop(): void
    {
        $this->connection->stopConsumer();
    }
}
