<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Serializer;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelopeInterface;

final class Serializer
{
    public function __construct(
        private MessageSerializerRegistry $registry,
        private Config $config
    ) {
    }

    public function serialize(
        object $message,
        ?MessageProperties $properties = null
    ): MessageEnvelopeInterface {
        return $this->getSerializer(\get_class($message))
            ->serialize($message, $properties ?: new MessageProperties());
    }

    public function deserialize(MessageEnvelopeInterface $envelope): object
    {
        return $this->getSerializer($envelope->getMessageClass())->deserialize($envelope);
    }

    private function getSerializer(string $messageClass): MessageSerializerInterface
    {
        return $this->registry->getSerializer(
            $this->config->getMessageConfig($messageClass)->getSerializerClass()
        );
    }
}
