<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message;

use Siemieniec\AmqpMessageBus\Message\Properties\MessageProperties;
use Siemieniec\AmqpMessageBus\Exception\MessageBusException;
use Siemieniec\AmqpMessageBus\Handler\HandlerRegistryInterface;
use Siemieniec\AmqpMessageBus\Rabbit\MessagePublisherInterface;
use Throwable;

final class MessageBus implements MessageBusInterface
{
    public function __construct(
        private HandlerRegistryInterface $handlerRegistry,
        private MessagePublisherInterface $messagePublisher
    ) {
    }

    public function execute(object $message): void
    {
        $handler = $this->handlerRegistry->getHandler($message);
        if (!\is_callable($handler)) {
            throw new MessageBusException(\sprintf('%s is not callable', \get_class($handler)));
        }

        try {
            $handler($message);
        } catch (Throwable $throwable) {
            throw new MessageBusException(
                \sprintf('%s failed to process message', \get_class($handler)),
                $throwable
            );
        }
    }

    public function executeAsync(object $message, ?MessageProperties $properties = null): void
    {
        try {
            $this->messagePublisher->publish($message, $properties);
        } catch (Throwable $throwable) {
            throw new MessageBusException('Failed to publish message', $throwable);
        }
    }
}
