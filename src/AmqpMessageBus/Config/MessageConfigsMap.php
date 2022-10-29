<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

use Siemieniec\AmqpMessageBus\Exception\MissingMessageConfigException;

class MessageConfigsMap extends AbstractMap
{
    public function current(): MessageConfig
    {
        return parent::current();
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): MessageConfig
    {
        return parent::offsetGet($offset);
    }

    protected function onMissingOffset(mixed $offset): mixed
    {
        throw new MissingMessageConfigException(
            \sprintf('Config has not been defined for message %s', $offset)
        );
    }

    protected function getValueType(): string
    {
        return MessageConfig::class;
    }
}
