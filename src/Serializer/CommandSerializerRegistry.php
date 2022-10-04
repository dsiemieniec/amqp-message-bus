<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Exception\MissingSerializerException;

class CommandSerializerRegistry
{
    /** @var array<string, CommandSerializerInterface> */
    private array $serializers;

    /**
     * @param CommandSerializerInterface[] $serializers
     */
    public function __construct(iterable $serializers)
    {
        foreach ($serializers as $serializer) {
            $this->serializers[\get_class($serializer)] = $serializer;
        }
    }

    public function getSerializer(string $serializerClass): CommandSerializerInterface
    {
        if (!isset($this->serializers[$serializerClass])) {
            throw new MissingSerializerException(\sprintf('Serializer %s does not exist', $serializerClass));
        }

        return $this->serializers[$serializerClass];
    }
}
