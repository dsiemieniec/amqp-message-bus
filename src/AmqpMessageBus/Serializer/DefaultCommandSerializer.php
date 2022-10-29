<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface;

class DefaultCommandSerializer extends AbstractCommandSerializer
{
    protected function init(): SerializerInterface
    {
        return new SymfonySerializer(
            [new ArrayDenormalizer(), new ObjectNormalizer()],
            [new JsonEncoder()]
        );
    }
}
