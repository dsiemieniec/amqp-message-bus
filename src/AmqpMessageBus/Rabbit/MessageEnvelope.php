<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Stringable;

final class MessageEnvelope implements MessageEnvelopeInterface
{
    private MessageProperties $properties;

    public function __construct(
        private Stringable|string $body,
        private string $messageClass,
        ?MessageProperties $properties = null
    ) {
        $this->properties = $properties ?: new MessageProperties();
    }

    public function getBody(): Stringable|string
    {
        return $this->body;
    }

    public function getProperties(): MessageProperties
    {
        return $this->properties;
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }
}
