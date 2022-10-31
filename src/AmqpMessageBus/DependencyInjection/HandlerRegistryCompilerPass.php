<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\DependencyInjection;

use ReflectionMethod;
use Siemieniec\AmqpMessageBus\Attributes\AsMessageHandler;
use Siemieniec\AmqpMessageBus\Exception\HandlerRegistryException;
use Siemieniec\AmqpMessageBus\Handler\HandlerRegistry;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class HandlerRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $handlerRegistryDefinition = $container->findDefinition(HandlerRegistry::class);

        foreach ($this->findHandlers($container) as $handlerId) {
            $handlerRegistryDefinition
                ->addMethodCall('registerHandler', [
                    $this->getMessageClass($container, (string)$handlerId),
                    new Reference($handlerId)
                ]);
        }
    }

    /**
     * @return string[]
     */
    private function findHandlers(ContainerBuilder $container): iterable
    {
        foreach ($container->getDefinitions() as $handlerId => $definition) {
            $reflectionClass = $container->getReflectionClass($definition->getClass());
            foreach ($reflectionClass?->getAttributes() ?? [] as $attribute) {
                if ($attribute->getName() === AsMessageHandler::class) {
                    yield $handlerId;
                }
            }
        }
    }

    private function getHandlerClass(ContainerBuilder $container, string $handlerId): string
    {
        while (true) {
            $definition = $container->findDefinition($handlerId);

            if (!$definition->getClass() && $definition instanceof ChildDefinition) {
                $handlerId = $definition->getParent();

                continue;
            }

            return $this->establishHandlerClass($definition, $handlerId);
        }
    }

    private function getMessageClass(ContainerBuilder $container, string $handlerId): string
    {
        $handlerClass = $this->getHandlerClass($container, $handlerId);
        $reflectionClass = $container->getReflectionClass($handlerClass);
        foreach ($reflectionClass?->getAttributes() ?? [] as $attribute) {
            if ($attribute->getName() === AsMessageHandler::class) {
                /** @var AsMessageHandler $asMessageHandler */
                $asMessageHandler = $attribute->newInstance();
                if ($asMessageHandler->getHandles() !== null) {
                    return $asMessageHandler->getHandles();
                }
            }
        }

        if ($reflectionClass === null) {
            throw new RuntimeException(
                \sprintf('Could not create ReflectionClass of %s during  compiler pass', $handlerClass)
            );
        }
        if (!$reflectionClass->hasMethod('__invoke')) {
            throw new HandlerRegistryException(
                \sprintf('__invoke method not implemented in %s', $handlerClass)
            );
        }
        $method = $reflectionClass->getMethod('__invoke');
        if (\count($method->getParameters()) !== 1) {
            throw new HandlerRegistryException(
                \sprintf(
                    'Invalid number of parameters of __invoke method in class %s. Expected 1 got %d',
                    $handlerClass,
                    \count($method->getParameters())
                )
            );
        }

        return $this->establishMessageClass($method, $handlerClass);
    }

    private function establishMessageClass(ReflectionMethod $method, string $handlerClass): string
    {
        $param = $method->getParameters()[0]->getType();
        if ($param === null) {
            throw new HandlerRegistryException(
                \sprintf(
                    'Unable to register handler. __invoke method parameter of class %s is missing type.',
                    $handlerClass
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

        return $param->getName();
    }

    private function establishHandlerClass(Definition $definition, string $handlerId): string
    {
        $handlerClass = $definition->getClass();
        if ($handlerClass === null) {
            throw new RuntimeException(
                \sprintf('Could not establish class of %s during compiler pass', $handlerId)
            );
        }

        return $handlerClass;
    }
}
