<?php

declare(strict_types=1);

namespace App\Rabbit\Message;

use App\Rabbit\Message\PublisherProperty\AppIdProperty;
use App\Rabbit\Message\PublisherProperty\ClusterIdProperty;
use App\Rabbit\Message\PublisherProperty\ContentEncodingProperty;
use App\Rabbit\Message\PublisherProperty\ContentTypeProperty;
use App\Rabbit\Message\PublisherProperty\CorrelationIdProperty;
use App\Rabbit\Message\PublisherProperty\DeliveryMode;
use App\Rabbit\Message\PublisherProperty\DeliveryModeProperty;
use App\Rabbit\Message\PublisherProperty\ExpirationProperty;
use App\Rabbit\Message\PublisherProperty\Headers;
use App\Rabbit\Message\PublisherProperty\MessageIdProperty;
use App\Rabbit\Message\PublisherProperty\PriorityProperty;
use App\Rabbit\Message\PublisherProperty\ReplyToProperty;
use App\Rabbit\Message\PublisherProperty\TimestampProperty;
use App\Rabbit\Message\PublisherProperty\TypeProperty;
use App\Rabbit\Message\PublisherProperty\UserIdProperty;

class PublisherPropertiesBuilder
{
    /** @var PublisherPropertyInterface[] */
    private array $properties = [];

    public function build(): PublisherProperties
    {
        return new PublisherProperties(...$this->properties);
    }

    private function add(PublisherPropertyInterface $property): self
    {
        $this->properties[] = $property;

        return $this;
    }

    public function contentType(string $value): self
    {
        return $this->add(new ContentTypeProperty($value));
    }

    public function contentEncoding(string $value): self
    {
        return $this->add(new ContentEncodingProperty($value));
    }

    public function headers(Headers $headers): self
    {
        return $this->add($headers);
    }

    public function deliveryMode(DeliveryMode $value): self
    {
        return $this->add(new DeliveryModeProperty($value));
    }

    public function priority(int $value): self
    {
        return $this->add(new PriorityProperty($value));
    }

    public function correlationId(string $value): self
    {
        return $this->add(new CorrelationIdProperty($value));
    }

    public function replyTo(string $value): self
    {
        return $this->add(new ReplyToProperty($value));
    }

    public function expiration(int $value): self
    {
        return $this->add(new ExpirationProperty($value));
    }

    public function messageId(string $value): self
    {
        return $this->add(new MessageIdProperty($value));
    }

    public function timestamp(int $timestamp): self
    {
        return $this->add(new TimestampProperty($timestamp));
    }

    public function type(string $value): self
    {
        return $this->add(new TypeProperty($value));
    }

    public function userId(string $value): self
    {
        return $this->add(new UserIdProperty($value));
    }

    public function appId(string $value): self
    {
        return $this->add(new AppIdProperty($value));
    }

    public function clusterId(string $value): self
    {
        return $this->add(new ClusterIdProperty($value));
    }
}
