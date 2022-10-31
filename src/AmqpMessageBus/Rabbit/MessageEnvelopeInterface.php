<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Stringable;

interface MessageEnvelopeInterface
{
    public function getMessageClass(): string;

    public function getBody(): Stringable|string;

    public function getProperties(): MessageProperties;
}
