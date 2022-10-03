<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Command\CommandBusInterface;
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
            $this->serializer->deserialize($message->getBody())
        );

        $connection->ack($message);
    }
}
