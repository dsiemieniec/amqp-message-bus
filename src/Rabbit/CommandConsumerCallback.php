<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandBusInterface;
use App\Exception\CommandBusException;
use App\Rabbit\Message\MessageEnvelope\MessageEnvelope;
use App\Rabbit\Message\MessageEnvelopeInterface;
use App\Rabbit\Message\PropertyKey;
use App\Serializer\Serializer;
use PhpAmqpLib\Message\AMQPMessage;

final class CommandConsumerCallback implements ConsumerCallbackInterface
{
    public function __construct(
        private Serializer $serializer,
        private CommandBusInterface $commandBus
    ) {
    }

    public function onMessage(AMQPMessage $message, ConnectionInterface $connection): void
    {
        try {
            $this->commandBus->execute(
                $this->serializer->deserialize(
                    $this->transformMessage($message)
                )
            );

            $connection->ack($message);
        } catch (CommandBusException $exception) {
            echo $exception->getMessage() . PHP_EOL;
            $connection->nack($message, true);
        }
    }

    private function transformMessage(AMQPMessage $message): MessageEnvelopeInterface
    {
        $properties = $message->get_properties();

        $builder = MessageEnvelope::builder(
            $message->getBody(),
            $properties[PropertyKey::Type->value] ?? ''
        );

        return $builder->build();
    }
}
