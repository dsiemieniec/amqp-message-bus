<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\Properties\CommandProperties;
use Stringable;

final class MessageEnvelope implements MessageEnvelopeInterface
{
    private CommandProperties $properties;

    public function __construct(
        private Stringable|string $body,
        private string $commandClass,
        ?CommandProperties $properties = null
    ) {
        $this->properties = $properties ?: new CommandProperties();
    }

    public function getBody(): Stringable|string
    {
        return $this->body;
    }

    public function getProperties(): CommandProperties
    {
        return $this->properties;
    }

    public function getCommandClass(): string
    {
        return $this->commandClass;
    }
}
