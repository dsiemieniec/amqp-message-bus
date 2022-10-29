<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Config\Queue;

class MessageConsumer implements MessageConsumerInterface
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