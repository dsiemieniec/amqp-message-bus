<?php

namespace App\Handler;

use App\Command\CommandInterface;
use ReflectionException;
use ReflectionObject;
use RuntimeException;

final class HandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @var array<string, HandlerInterface>
     */
    private array $registry = [];

    /**
     * @param HandlerInterface[] $handlers
     * @throws ReflectionException
     */
    public function __construct(iterable $handlers)
    {
        // ToDo: compiler pass
        foreach ($handlers as $handler) {
            $reflectionClass = new ReflectionObject($handler);
            $method = $reflectionClass->getMethod('__invoke');
            $param = $method->getParameters()[0]->getType();
            if (isset($this->registry[$param->getName()])) {
                // ToDo: custom exception
                throw new RuntimeException(\sprintf('%s already has handler', $param->getName()));
            }
            $this->registry[$param->getName()] = $handler;
        }
    }

    public function getHandler(CommandInterface $command): HandlerInterface
    {
        // ToDo: throw custom exception if handler does not exist
        return $this->registry[\get_class($command)];
    }
}
