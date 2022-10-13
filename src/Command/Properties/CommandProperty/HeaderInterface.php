<?php

declare(strict_types=1);

namespace App\Command\Properties\CommandProperty;

use JsonSerializable;

interface HeaderInterface extends JsonSerializable
{
    public function getName(): string;
    public function getValue(): string;
}
