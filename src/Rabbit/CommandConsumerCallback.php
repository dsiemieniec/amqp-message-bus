<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandBusInterface;
use App\Exception\CommandBusException;
use App\Rabbit\MessageEnvelope;
use App\Rabbit\MessageEnvelopeInterface;
use App\Command\Properties\PropertyKey;
use App\Serializer\Serializer;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

final class CommandConsumerCallback implements ConsumerCallbackInterface
{
    public function __construct(
        private Serializer $serializer,
        private CommandBusInterface $commandBus,
        private MessageTransformerInterface $transformer,
        private LoggerInterface $logger
    ) {
    }

    public function onMessage(AMQPMessage $message, ConnectionInterface $connection): void
    {
        try {
            $this->commandBus->execute(
                $this->serializer->deserialize(
                    $this->transformer->transformMessage($message)
                )
            );

            $connection->ack($message);
        } catch (CommandBusException $exception) {
            $this->logger->error($exception->getMessage());
            $connection->nack($message, true);
        }
    }
}
