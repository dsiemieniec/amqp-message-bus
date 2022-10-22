<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Command\Properties;

use Siemieniec\AsyncCommandBus\Command\Properties\PropertyKey;
use ArrayAccess;

interface CommandPropertyInterface extends ArrayAccess
{
    public function getKey(): PropertyKey;

    /**
     * @return int|string|array<string, string>
     */
    public function getValue(): int|string|array;
}
