<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;
use Stringable;

interface MessageEnvelopeInterface
{
    public function getCommandClass(): string;

    public function getBody(): Stringable|string;

    public function getProperties(): CommandProperties;
}
