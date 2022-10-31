<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

class MessageConfig
{
    public const DEFAULT_REQUEUE_ON_FAILURE = false;

    public function __construct(
        private string $messageClass,
        private string $serializerClass,
        private MessagePublisherConfig $publisherConfig,
        private bool $requeueOnFailure = self::DEFAULT_REQUEUE_ON_FAILURE
    ) {
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    public function getSerializerClass(): string
    {
        return $this->serializerClass;
    }

    public function getPublisherConfig(): MessagePublisherConfig
    {
        return $this->publisherConfig;
    }

    public function requeueOnFailure(): bool
    {
        return $this->requeueOnFailure;
    }
}
