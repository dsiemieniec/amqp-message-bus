<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Command\Properties;

final class BasicHeader implements HeaderInterface
{
    public function __construct(
        private string $key,
        private string $value
    ) {
    }

    public function getName(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
