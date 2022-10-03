<?php

declare(strict_types=1);

namespace App\Rabbit\Message\PublisherProperty;

use JsonSerializable;

interface HeaderInterface extends JsonSerializable
{
    public function getName(): string;
    public function getValue(): string;
}
