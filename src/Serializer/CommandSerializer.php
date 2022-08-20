<?php

namespace App\Serializer;

use App\Command\CommandInterface;
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
        return \json_encode([
            'metadata' => [
                'class' => \get_class($command)
            ],
            'command' => $this->serializer->normalize($command)
        ]);
    }

    public function deserialize(string $serializedCommand): CommandInterface
    {
        $data = \json_decode($serializedCommand, true);

        return $this->serializer->denormalize($data['command'], $data['metadata']['class']);
    }
}
