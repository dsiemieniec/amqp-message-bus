<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;

interface MessagePublisherInterface
{
    public function publish(object $message, ?MessageProperties $messageProperties = null): void;
}
