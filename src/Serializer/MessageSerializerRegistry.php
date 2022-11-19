<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Siemieniec\AmqpMessageBus\Exception\MissingSerializerException;

final class MessageSerializerRegistry
{
    /** @var array<string, MessageSerializerInterface> */
    private array $serializers;

    public function registerSerializer(string $serializerClass, MessageSerializerInterface $serializer): void
    {
        $this->serializers[$serializerClass] = $serializer;
    }

    public function getSerializer(string $serializerClass): MessageSerializerInterface
    {
        if (!isset($this->serializers[$serializerClass])) {
            throw new MissingSerializerException(\sprintf('Serializer %s does not exist', $serializerClass));
        }

        return $this->serializers[$serializerClass];
    }
}
