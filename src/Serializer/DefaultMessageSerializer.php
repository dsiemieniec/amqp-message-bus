<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DefaultMessageSerializer extends AbstractMessageSerializer
{
    protected function init(): SerializerInterface
    {
        return new SymfonySerializer(
            [
                new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => DATE_ISO8601]),
                new ArrayDenormalizer(),
                new ObjectNormalizer()
            ],
            [
                new JsonEncoder()
            ]
        );
    }
}
