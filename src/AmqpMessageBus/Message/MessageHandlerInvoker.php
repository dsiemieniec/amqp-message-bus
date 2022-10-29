<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message;

use Siemieniec\AmqpMessageBus\Exception\MessageBusException;
use Siemieniec\AmqpMessageBus\Handler\HandlerRegistryInterface;
use Throwable;

final class MessageHandlerInvoker implements MessageHandlerInvokerInterface
{
    public function __construct(
        private HandlerRegistryInterface $handlerRegistry,
    ) {
    }

    public function handle(object $message): void
    {
        $handler = $this->getHandler($message);

        try {
            $handler($message);
        } catch (Throwable $throwable) {
            throw new MessageBusException(
                \sprintf('%s failed to process message', \get_debug_type($handler)),
                $throwable
            );
        }
    }

    private function getHandler(object $message): callable
    {
        $handler = $this->handlerRegistry->getHandler($message);
        if (!\is_callable($handler)) {
            throw new MessageBusException(\sprintf('%s is not callable', \get_debug_type($handler)));
        }

        return $handler;
    }
}
