<?php

declare(strict_types=1);

namespace App\Rabbit\Message\MessageEnvelope;

use App\Rabbit\Message\MessageEnvelopeInterface;
use App\Rabbit\Message\PublisherProperties;
use Stringable;

final class MessageEnvelope implements MessageEnvelopeInterface
{
    private PublisherProperties $properties;

    public function __construct(
        private Stringable|string $body,
        ?PublisherProperties $properties = null
    ) {
        $this->properties = $properties ?: new PublisherProperties();
    }

    public static function builder(Stringable|string $body): MessageEnvelopeBuilder
    {
        return new MessageEnvelopeBuilder($body);
    }

    public function getBody(): Stringable|string
    {
        return $this->body;
    }

    public function getProperties(): PublisherProperties
    {
        return $this->properties;
    }
}
