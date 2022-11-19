<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message\Properties;

interface HeaderInterface
{
    public function getName(): string;

    public function getValue(): string;
}
