<?php

declare(strict_types=1);

namespace App\Command\Properties;

use App\Command\Properties\CommandProperty\AppIdProperty;
use App\Command\Properties\CommandProperty\ClusterIdProperty;
use App\Command\Properties\CommandProperty\ContentEncodingProperty;
use App\Command\Properties\CommandProperty\ContentTypeProperty;
use App\Command\Properties\CommandProperty\CorrelationIdProperty;
use App\Command\Properties\CommandProperty\DeliveryMode;
use App\Command\Properties\CommandProperty\DeliveryModeProperty;
use App\Command\Properties\CommandProperty\ExpirationProperty;
use App\Command\Properties\CommandProperty\Headers;
use App\Command\Properties\CommandProperty\HeadersBuilder;
use App\Command\Properties\CommandProperty\MessageIdProperty;
use App\Command\Properties\CommandProperty\PriorityProperty;
use App\Command\Properties\CommandProperty\ReplyToProperty;
use App\Command\Properties\CommandProperty\TimestampProperty;
use App\Command\Properties\CommandProperty\UserIdProperty;

class CommandPropertiesBuilder
{
    /** @var CommandPropertyInterface[] */
    private array $properties = [];
    private HeadersBuilder $headersBuilder;

    public function __construct()
    {
        $this->headersBuilder = Headers::builder();
    }

    public function build(): CommandProperties
    {
        $this->add($this->headersBuilder->build());

        return new CommandProperties(...$this->properties);
    }

    private function add(CommandPropertyInterface $property): self
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

    public function addHeader(string $name, string $value): self
    {
        $this->headersBuilder->add($name, $value);

        return $this;
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
