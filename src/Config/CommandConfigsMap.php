<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingCommandConfigException;
use InvalidArgumentException;

class CommandConfigsMap extends AbstractMap
{
    public function current(): CommandConfig
    {
        return parent::current();
    }

    public function offsetGet(mixed $offset): CommandConfig
    {
        return parent::offsetGet($offset);
    }

    protected function assertValueType(mixed $value): void
    {
        if (!($value instanceof CommandConfig)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Value must be of type %s. %s given',
                    CommandConfig::class,
                    \get_debug_type($value)
                )
            );
        }
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingCommandConfigException(
            \sprintf('Config has not been defined for command %s', $offset)
        );
    }
}
