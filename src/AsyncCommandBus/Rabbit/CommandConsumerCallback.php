<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use Siemieniec\AsyncCommandBus\Rabbit\ConnectionInterface;
use Siemieniec\AsyncCommandBus\Rabbit\ConsumerCallbackInterface;
use Siemieniec\AsyncCommandBus\Rabbit\MessageTransformerInterface;
use Siemieniec\AsyncCommandBus\Command\CommandBusInterface;
use Siemieniec\AsyncCommandBus\Config\Config;
use Siemieniec\AsyncCommandBus\Exception\CommandBusException;
use Siemieniec\AsyncCommandBus\Serializer\Serializer;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

final class CommandConsumerCallback implements ConsumerCallbackInterface
{
    public function __construct(
        private Serializer $serializer,
        private CommandBusInterface $commandBus,
        private MessageTransformerInterface $transformer,
        private LoggerInterface $logger,
        private Config $config
    ) {
    }

    public function onMessage(AMQPMessage $message, ConnectionInterface $connection): void
    {
        try {
            $command = $this->serializer->deserialize(
                $this->transformer->transformMessage($message)
            );

            $this->commandBus->execute($command);

            $connection->ack($message);
        } catch (CommandBusException $exception) {
            $this->logger->error($exception->getMessage());
            $connection->nack(
                $message,
                $this->config->getCommandConfig(\get_class($command))->requeueOnFailure()
            );
        }
    }
}
