<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandBusInterface;
use App\Rabbit\Message\MessageEnvelope\MessageEnvelope;
use App\Rabbit\Message\MessageEnvelopeInterface;
use App\Rabbit\Message\PropertyKey;
use App\Serializer\CommandSerializerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CommandConsumerCallback implements ConsumerCallbackInterface
{
    public function __construct(
        private CommandSerializerInterface $serializer,
        private CommandBusInterface $commandBus
    ) {
    }

    public function onMessage(AMQPMessage $message, ConnectionInterface $connection): void
    {
        $this->commandBus->execute(
            $this->serializer->deserialize(
                $this->transformMessage($message)
            )
        );

        $connection->ack($message);
    }

    private function transformMessage(AMQPMessage $message): MessageEnvelopeInterface
    {
        $builder = MessageEnvelope::builder($message->getBody());

        $properties = $message->get_properties();
        $builder->type($properties[PropertyKey::Type->value] ?? '');

        return $builder->build();
    }
}
