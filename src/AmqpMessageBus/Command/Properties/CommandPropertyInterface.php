<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Command\Properties;

use ArrayAccess;

interface CommandPropertyInterface extends ArrayAccess
{
    public function getKey(): PropertyKey;

    /**
     * @return int|string|array<string, string>
     */
    public function getValue(): int|string|array;
}
