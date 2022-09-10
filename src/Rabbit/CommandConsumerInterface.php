<?php

namespace App\Rabbit;

interface CommandConsumerInterface
{
    public function consume(string $name, ConsumerLimits $limits): void;
}
