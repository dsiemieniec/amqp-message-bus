<?php

declare(strict_types=1);

namespace App\Rabbit\Message\MessageEnvelope;

use App\Rabbit\Message\PublisherProperties;
use App\Rabbit\Message\PublisherPropertiesBuilder;
use App\Rabbit\Message\PublisherProperty\DeliveryMode;
use App\Rabbit\Message\PublisherProperty\Headers;
use App\Rabbit\Message\PublisherProperty\HeadersBuilder;
use Stringable;

final class MessageEnvelopeBuilder
{
    private PublisherPropertiesBuilder $propertiesBuilder;
    private HeadersBuilder $headersBuilder;

    public function __construct(
        private Stringable|string $body,
        private string $commandClass
    ) {
        $this->propertiesBuilder = PublisherProperties::builder();
        $this->headersBuilder = Headers::builder();
    }

    public function build(): MessageEnvelope
    {
        $this->propertiesBuilder->headers($this->headersBuilder->build());

        return new MessageEnvelope(
            $this->body,
            $this->commandClass,
            $this->propertiesBuilder->build()
        );
    }

    public function contentType(string $value): self
    {
        $this->propertiesBuilder->contentType($value);

        return $this;
    }

    public function contentEncoding(string $value): self
    {
        $this->propertiesBuilder->contentEncoding($value);

        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headersBuilder->add($name, $value);

        return $this;
    }

    public function deliveryMode(DeliveryMode $value): self
    {
        $this->propertiesBuilder->deliveryMode($value);

        return $this;
    }

    public function priority(int $value): self
    {
        $this->propertiesBuilder->priority($value);

        return $this;
    }

    public function correlationId(string $value): self
    {
        $this->propertiesBuilder->correlationId($value);

        return $this;
    }

    public function replyTo(string $value): self
    {
        $this->propertiesBuilder->replyTo($value);

        return $this;
    }

    public function expiration(int $value): self
    {
        $this->propertiesBuilder->expiration($value);

        return $this;
    }

    public function messageId(string $value): self
    {
        $this->propertiesBuilder->messageId($value);

        return $this;
    }

    public function timestamp(int $timestamp): self
    {
        $this->propertiesBuilder->timestamp($timestamp);

        return $this;
    }

    public function type(string $value): self
    {
        $this->propertiesBuilder->type($value);

        return $this;
    }

    public function userId(string $value): self
    {
        $this->propertiesBuilder->userId($value);

        return $this;
    }

    public function appId(string $value): self
    {
        $this->propertiesBuilder->appId($value);

        return $this;
    }

    public function clusterId(string $value): self
    {
        $this->propertiesBuilder->clusterId($value);

        return $this;
    }
}
