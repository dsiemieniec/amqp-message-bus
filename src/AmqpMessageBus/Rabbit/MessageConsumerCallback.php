<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Message\MessageBusInterface;
use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Exception\MessageBusException;
use Siemieniec\AmqpMessageBus\Serializer\Serializer;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

final class MessageConsumerCallback implements ConsumerCallbackInterface
{
    public function __construct(
        private Serializer $serializer,
        private MessageBusInterface $messageBus,
        private MessageTransformerInterface $transformer,
        private LoggerInterface $logger,
        private Config $config
    ) {
    }

    public function onMessage(AMQPMessage $amqpMessage, ConnectionInterface $connection): void
    {
        try {
            $message = $this->serializer->deserialize(
                $this->transformer->transformMessage($amqpMessage)
            );

            $this->messageBus->execute($message);

            $connection->ack($amqpMessage);
        } catch (MessageBusException $exception) {
            $this->logger->error($exception->getMessage());
            $connection->nack(
                $amqpMessage,
                $this->config->getMessageConfig(\get_class($message))->requeueOnFailure()
            );
        }
    }
}
