<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

interface MessageConsumerInterface
{
    public function consume(): void;

    public function stop(): void;
}
