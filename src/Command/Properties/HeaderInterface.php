<?php

declare(strict_types=1);

namespace App\Command\Properties;

interface HeaderInterface
{
    public function getName(): string;
    public function getValue(): string;
}
