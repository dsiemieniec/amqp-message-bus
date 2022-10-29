<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message\Properties;

use ArrayAccess;

interface MessagePropertyInterface extends ArrayAccess
{
    public function getKey(): PropertyKey;

    /**
     * @return int|string|array<string, string>
     */
    public function getValue(): int|string|array;
}
