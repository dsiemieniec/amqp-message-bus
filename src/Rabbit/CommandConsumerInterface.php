<?php

declare(strict_types=1);

namespace App\Rabbit;

interface CommandConsumerInterface
{
    public function consume(ConsumerLimits $limits): void;
    public function stop(): void;
}
