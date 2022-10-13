<?php

declare(strict_types=1);

namespace App\Command\Properties;

class CommandPropertiesBuilder
{
    private CommandProperties $properties;

    public function __construct()
    {
        $this->properties = new CommandProperties();
    }

    public function build(): CommandProperties
    {
        return $this->properties;
    }

    public function contentType(string $value): self
    {
        $this->properties[PropertyKey::ContentType] = $value;

        return $this;
    }

    public function contentEncoding(string $value): self
    {
        $this->properties[PropertyKey::ContentEncoding] = $value;

        return $this;
    }

    public function deliveryMode(DeliveryMode $value): self
    {
        $this->properties[PropertyKey::DeliveryMode] = $value;

        return $this;
    }

    public function priority(int $value): self
    {
        $this->properties[PropertyKey::Priority] = $value;

        return $this;
    }

    public function correlationId(string $value): self
    {
        $this->properties[PropertyKey::CorrelationId] = $value;

        return $this;
    }

    public function replyTo(string $value): self
    {
        $this->properties[PropertyKey::ReplyTo] = $value;

        return $this;
    }

    public function expiration(int $value): self
    {
        $this->properties[PropertyKey::Expiration] = $value;

        return $this;
    }

    public function messageId(string $value): self
    {
        $this->properties[PropertyKey::MessageId] = $value;

        return $this;
    }

    public function timestamp(int $value): self
    {
        $this->properties[PropertyKey::Timestamp] = $value;

        return $this;
    }

    public function userId(string $value): self
    {
        $this->properties[PropertyKey::UserId] = $value;

        return $this;
    }

    public function appId(string $value): self
    {
        $this->properties[PropertyKey::AppId] = $value;

        return $this;
    }

    public function clusterId(string $value): self
    {
        $this->properties[PropertyKey::ClusterId] = $value;

        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->properties[PropertyKey::Headers] = new BasicHeader($name, $value);

        return $this;
    }
}
