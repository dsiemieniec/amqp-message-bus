<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

interface CommandConsumerInterface
{
    public function consume(): void;
    public function stop(): void;
}
