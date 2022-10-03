<?php

namespace App\Handler;

use App\Command\CommandInterface;
use App\Exception\HandlerDuplicateException;
use App\Exception\HandlerMissingException;
use App\Exception\HandlerRegistryException;
use ReflectionNamedType;
use ReflectionObject;

final class HandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @var array<string, HandlerInterface>
     */
    private array $registry = [];

    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $reflectionClass = new ReflectionObject($handler);
            if (!$reflectionClass->hasMethod('__invoke')) {
                throw new HandlerRegistryException(
                    \sprintf('__invoke method not implemented in %s', \get_class($handler))
                );
            }
            $method = $reflectionClass->getMethod('__invoke');
            if (\count($method->getParameters()) !== 1) {
                throw new HandlerRegistryException(
                    \sprintf(
                        'Invalid number of parameters of __invoke method in class %s. Expected 1 got %d',
                        \get_class($handler),
                        \count($method->getParameters())
                    )
                );
            }
            $param = $method->getParameters()[0]->getType();
            if ($param === null) {
                throw new HandlerRegistryException(
                    \sprintf(
                        'Unable to register handler. __invoke method parameter of class %s is missing type.',
                        \get_class($handler)
                    )
                );
            }
            if (!($param instanceof ReflectionNamedType)) {
                throw new HandlerRegistryException(
                    \sprintf(
                        'Unable to register handler. Got invalid reflection parameter type of %s',
                        \get_class($param)
                    )
                );
            }

            if (isset($this->registry[$param->getName()])) {
                throw new HandlerDuplicateException(\sprintf('%s already has handler', $param->getName()));
            }
            $this->registry[$param->getName()] = $handler;
        }
    }

    public function getHandler(CommandInterface $command): HandlerInterface
    {
        $commandClass = \get_class($command);
        if (!isset($this->registry[$commandClass])) {
            throw new HandlerMissingException(\sprintf('Handler not registered for command %s', $commandClass));
        }

        return $this->registry[$commandClass];
    }
}
