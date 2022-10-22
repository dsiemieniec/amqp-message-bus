<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Exception\MissingCommandConfigException;
use Siemieniec\AsyncCommandBus\Config\AbstractMap;
use Siemieniec\AsyncCommandBus\Config\CommandConfig;

class CommandConfigsMap extends AbstractMap
{
    public function current(): CommandConfig
    {
        return parent::current();
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): CommandConfig
    {
        return parent::offsetGet($offset);
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingCommandConfigException(
            \sprintf('Config has not been defined for command %s', $offset)
        );
    }

    protected function getValueType(): string
    {
        return CommandConfig::class;
    }
}
