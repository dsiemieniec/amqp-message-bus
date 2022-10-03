<?php

declare(strict_types=1);

namespace App\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;

interface ConsumerCallbackInterface
{
    public function onMessage(AMQPMessage $message, ConnectionInterface $connection): void;
}
