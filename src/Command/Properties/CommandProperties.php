<?php

declare(strict_types=1);

namespace App\Command\Properties;

use App\Command\Properties\CommandProperty\AbstractIntegerValueCommandProperty;
use App\Command\Properties\CommandProperty\AbstractStringValueCommandProperty;
use App\Command\Properties\CommandProperty\DeliveryMode;
use App\Command\Properties\CommandProperty\DeliveryModeProperty;
use App\Command\Properties\CommandProperty\Headers;

class CommandProperties
{
    /**
     * @var array<string, CommandPropertyInterface>
     */
    private array $properties = [];

    public function __construct(CommandPropertyInterface ...$properties)
    {
        foreach ($properties as $property) {
            $this->add($property);
        }
    }

    public static function builder(): CommandPropertiesBuilder
    {
        return new CommandPropertiesBuilder();
    }

    public function add(CommandPropertyInterface $property): void
    {
        $this->properties[$property->getKey()->value] = $property;
    }

    /**
     * @return CommandPropertyInterface[]
     */
    public function all(): array
    {
        return $this->properties;
    }

    public function get(PropertyKey $key): ?CommandPropertyInterface
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

        return $property instanceof AbstractStringValueCommandProperty ? $property->getValue() : null;
    }

    private function getIntegerValueProperty(PropertyKey $key): ?int
    {
        $property = $this->get($key);

        return $property instanceof AbstractIntegerValueCommandProperty ? $property->getValue() : null;
    }
}
