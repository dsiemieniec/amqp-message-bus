<?php

declare(strict_types=1);

namespace App\Rabbit\Message;

use Stringable;

interface MessageEnvelopeInterface
{
    public function getCommandClass(): string;
    public function getBody(): Stringable|string;
    public function getProperties(): PublisherProperties;
}
