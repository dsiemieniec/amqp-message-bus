<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Exception\MissingSerializerException;

final class CommandSerializerRegistry
{
    /** @var array<string, CommandSerializerInterface> */
    private array $serializers;

    public function registerSerializer(string $serializerClass, CommandSerializerInterface $serializer): void
    {
        $this->serializers[$serializerClass] = $serializer;
    }

    public function getSerializer(string $serializerClass): CommandSerializerInterface
    {
        if (!isset($this->serializers[$serializerClass])) {
            throw new MissingSerializerException(\sprintf('Serializer %s does not exist', $serializerClass));
        }

        return $this->serializers[$serializerClass];
    }
}
