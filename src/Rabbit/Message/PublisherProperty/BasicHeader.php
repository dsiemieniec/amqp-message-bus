<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

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

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return ['key' => $this->key, 'value' => $this->value];
    }
}
