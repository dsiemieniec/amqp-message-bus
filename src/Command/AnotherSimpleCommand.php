<?php

declare(strict_types=1);

namespace App\Command;

class AnotherSimpleCommand implements CommandInterface
{
    public function __construct(
        private string $firstText,
        private string $secondText
    ) {
    }

    public function getFirstText(): string
    {
        return $this->firstText;
    }

    public function getSecondText(): string
    {
        return $this->secondText;
    }
}
