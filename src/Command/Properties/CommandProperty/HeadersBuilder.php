<?php

declare(strict_types=1);

namespace App\Command\Properties\CommandProperty;

class HeadersBuilder
{
    /**
     * @var HeaderInterface[]
     */
    public array $headers = [];

    public function add(string $key, string $value): self
    {
        $this->headers[] = new BasicHeader($key, $value);

        return $this;
    }

    public function build(): Headers
    {
        return new Headers(...$this->headers);
    }
}
