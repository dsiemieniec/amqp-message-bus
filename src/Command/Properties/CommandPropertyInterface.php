<?php

declare(strict_types=1);

namespace App\Command\Properties;

use ArrayAccess;

interface CommandPropertyInterface extends ArrayAccess
{
    public function getKey(): PropertyKey;

    /**
     * @return int|string|array<string, string>
     */
    public function getValue(): int|string|array;
}
