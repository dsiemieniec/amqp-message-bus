<?php

namespace App\Serializer;

use App\Command\CommandInterface;
use App\Exception\DeserializationException;
use App\Exception\SerializationException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CommandSerializer implements CommandSerializerInterface
{
    private Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new ObjectNormalizer()]);
    }

    public function serialize(CommandInterface $command): string
    {
        $result = \json_encode([
            'metadata' => [
                'class' => \get_class($command)
            ],
            'command' => $this->serializer->normalize($command)
        ]);

        if ($result === false) {
            throw new SerializationException(\json_last_error_msg());
        }

        return $result;
    }

    public function deserialize(string $serializedCommand): CommandInterface
    {
        $data = \json_decode($serializedCommand, true);

        if ($data === false) {
            throw new DeserializationException(\json_last_error_msg());
        }

        return $this->serializer->denormalize($data['command'], $data['metadata']['class']);
    }
}
