<?php

namespace App\Rabbit;

interface CommandConsumerInterface
{
    public function consume(): void;
}
