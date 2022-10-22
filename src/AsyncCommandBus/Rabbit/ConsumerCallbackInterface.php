<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use Siemieniec\AsyncCommandBus\Rabbit\ConnectionInterface;

interface ConsumerCallbackInterface
{
    public function onMessage(AMQPMessage $message, ConnectionInterface $connection): void;
}
