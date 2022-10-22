<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use Siemieniec\AsyncCommandBus\Rabbit\CommandConsumerInterface;
use Siemieniec\AsyncCommandBus\Rabbit\ConsumerCallbackInterface;
use Siemieniec\AsyncCommandBus\Rabbit\RabbitConnection;
use Siemieniec\AsyncCommandBus\Config\Queue;

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
        if ($this->queueConfig->canAutoDeclare()) {
            $this->connection->declareQueue($this->queueConfig);
        }

        $this->connection->consume($this->queueConfig, $this->callback);
    }

    public function stop(): void
    {
        $this->connection->stopConsumer();
    }
}
