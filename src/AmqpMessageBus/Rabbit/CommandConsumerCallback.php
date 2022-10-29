<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Command\CommandBusInterface;
use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Exception\CommandBusException;
use Siemieniec\AmqpMessageBus\Serializer\Serializer;
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
