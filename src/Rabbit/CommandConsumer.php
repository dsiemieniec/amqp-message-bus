<?php

namespace App\Rabbit;

class CommandConsumer implements CommandConsumerInterface
{
    private const QUEUE_NAME = 'command_bus';

    public function __construct(
    ) {
    }

    public function consume(): void
    {
        //$this->connection->consume(new ConsumerParameters(new Queue(self::QUEUE_NAME)), $this->callback);
    }
}

