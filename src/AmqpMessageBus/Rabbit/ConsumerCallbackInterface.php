<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;

interface ConsumerCallbackInterface
{
    public function onMessage(AMQPMessage $message, ConnectionInterface $connection): void;
}
