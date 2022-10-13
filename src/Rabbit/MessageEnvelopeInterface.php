<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\Properties\CommandProperties;
use Stringable;

interface MessageEnvelopeInterface
{
    public function getCommandClass(): string;
    public function getBody(): Stringable|string;
    public function getProperties(): CommandProperties;
}
