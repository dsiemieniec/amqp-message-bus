<?php

declare(strict_types = 1);

namespace Siemieniec\AsyncCommandBus\Serializer;

use Siemieniec\AsyncCommandBus\Serializer\MissingSerializerException;

use function sprintf;

final class CommandSerializerRegistry
{

    /** @var array<string, \Siemieniec\AsyncCommandBus\Serializer\CommandSerializerInterface> */
    private array $serializers;

    public function registerSerializer(string $serializerClass, CommandSerializerInterface $serializer): void
    {
        $this->serializers[$serializerClass] = $serializer;
    }

    public function getSerializer(string $serializerClass): CommandSerializerInterface
    {
        if (!isset($this->serializers[$serializerClass])) {
            throw new \Siemieniec\AsyncCommandBus\Serializer\MissingSerializerException(
                \sprintf('Serializer %s does not exist', $serializerClass)
            );
        }

        return $this->serializers[$serializerClass];
    }

}
