<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message;

use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Exception\HandlersFailedException;
use Siemieniec\AmqpMessageBus\Handler\MessageHandlerInvokerInterface;
use Siemieniec\AmqpMessageBus\Rabbit\ConnectionInterface;
use Siemieniec\AmqpMessageBus\Rabbit\ConsumerCallbackInterface;
use Siemieniec\AmqpMessageBus\Rabbit\MessageTransformerInterface;
use Siemieniec\AmqpMessageBus\Serializer\Serializer;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Throwable;

final class MessageConsumerCallback implements ConsumerCallbackInterface
{
    public function __construct(
        private Serializer $serializer,
        private MessageHandlerInvokerInterface $handlerInvoker,
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

            $this->handlerInvoker->handle($message);

            $amqpMessage->ack();
        } catch (HandlersFailedException $exception) {
            $this->logger->error($exception->getMessage(), [
                'trace' => $exception->getMessage(),
                'nestedExceptions' => \array_map(
                    fn(Throwable $throwable): array => [
                        'message' => $throwable->getMessage(),
                        'trace' => $throwable->getTraceAsString()
                    ],
                    $exception->getNestedExceptions()
                )
            ]);
            if (isset($message)) {
                $amqpMessage->nack(
                    $this->config->getMessageConfig(\get_class($message))->requeueOnFailure()
                );
            }
        }
    }
}
