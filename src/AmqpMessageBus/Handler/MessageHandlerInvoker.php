<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Handler;

use Siemieniec\AmqpMessageBus\Exception\HandlersFailedException;
use Throwable;

final class MessageHandlerInvoker implements MessageHandlerInvokerInterface
{
    public function __construct(
        private HandlerRegistryInterface $handlerRegistry,
    ) {
    }

    public function handle(object $message): void
    {
        $handlers = $this->handlerRegistry->getHandlers($message);

        $exceptions = [];
        foreach ($handlers as $handler) {
            try {
                $handler($message);
            } catch (Throwable $throwable) {
                $exceptions[] = $throwable;
            }
        }

        if (\count($exceptions) > 0) {
            throw new HandlersFailedException(
                \sprintf('Some handlers of %s failed', \get_class($message)),
                $exceptions
            );
        }
    }
}
