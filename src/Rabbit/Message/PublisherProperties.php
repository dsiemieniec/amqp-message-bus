<?php

declare(strict_types=1);

namespace App\Rabbit\Message;

use App\Rabbit\Message\PublisherProperty\AbstractIntegerValuePublisherProperty;
use App\Rabbit\Message\PublisherProperty\AbstractStringValuePublisherProperty;
use App\Rabbit\Message\PublisherProperty\DeliveryMode;
use App\Rabbit\Message\PublisherProperty\DeliveryModeProperty;
use App\Rabbit\Message\PublisherProperty\Headers;

class PublisherProperties
{
    /**
     * @var array<string, PublisherPropertyInterface>
     */
    private array $properties = [];

    public function __construct(PublisherPropertyInterface ...$properties)
    {
        foreach ($properties as $property) {
            $this->add($property);
        }
    }

    public static function builder(): PublisherPropertiesBuilder
    {
        return new PublisherPropertiesBuilder();
    }

    public function add(PublisherPropertyInterface $property): void
    {
        $this->properties[$property->getKey()->value] = $property;
    }

    /**
     * @return PublisherPropertyInterface[]
     */
    public function all(): array
    {
        return $this->properties;
    }

    public function get(PropertyKey $key): ?PublisherPropertyInterface
    {
        return $this->properties[$key->value] ?? null;
    }

    public function getHeaders(): Headers
    {
        $headers = $this->get(PropertyKey::Headers);

        return $headers instanceof Headers ? $headers : new Headers();
    }

    public function getContentType(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::ContentType);
    }

    public function getContentEncoding(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::ContentEncoding);
    }

    public function getDeliveryMode(): ?DeliveryMode
    {
        $property = $this->get(PropertyKey::DeliveryMode);

        return $property instanceof DeliveryModeProperty ? $property->getValue() : null;
    }

    public function getPriority(): ?int
    {
        return $this->getIntegerValueProperty(PropertyKey::Priority);
    }

    public function getCorrelationId(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::CorrelationId);
    }

    public function getReplyTo(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::ReplyTo);
    }

    public function getExpiration(): ?int
    {
        return $this->getIntegerValueProperty(PropertyKey::Expiration);
    }

    public function getMessageId(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::MessageId);
    }

    public function getTimestamp(): ?int
    {
        return $this->getIntegerValueProperty(PropertyKey::Timestamp);
    }

    public function getType(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::Type);
    }

    public function getUserId(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::UserId);
    }

    public function getAppId(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::AppId);
    }

    public function getClusterId(): ?string
    {
        return $this->getStringValueProperty(PropertyKey::ClusterId);
    }

    private function getStringValueProperty(PropertyKey $key): ?string
    {
        $property = $this->get($key);

        return $property instanceof AbstractStringValuePublisherProperty ? $property->getValue() : null;
    }

    private function getIntegerValueProperty(PropertyKey $key): ?int
    {
        $property = $this->get($key);

        return $property instanceof AbstractIntegerValuePublisherProperty ? $property->getValue() : null;
    }
}
