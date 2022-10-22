<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\DependencyInjection;

use App\Kernel;
use ReflectionNamedType;
use Siemieniec\AsyncCommandBus\DependencyInjection\HandlerRegistryException;
use Siemieniec\AsyncCommandBus\DependencyInjection\RuntimeException;
use Siemieniec\AsyncCommandBus\Handler\HandlerRegistry;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function count;
use function get_class;
use function sprintf;

final class HandlerRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $handlerRegistryDefinition = $container->findDefinition(HandlerRegistry::class);

        $handlerIds = $container->findTaggedServiceIds(Kernel::APP_COMMAND_HANDLER_TAG);

        foreach ($handlerIds as $handlerId => $tags) {
            $handlerRegistryDefinition
                ->addMethodCall('registerHandler', [
                    $this->getCommandClass($container, (string)$handlerId),
                    new Reference($handlerId),
                ]);
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

            $handlerClass = $definition->getClass();

            if ($handlerClass === null) {
                throw new \Siemieniec\AsyncCommandBus\DependencyInjection\RuntimeException(
                    \sprintf('Could not establish class of %s during compiler pass', $handlerId)
                );
            }

            return $handlerClass;
        }
    }

    private function getCommandClass(ContainerBuilder $container, string $handlerId): string
    {
        $handlerClass = $this->getHandlerClass($container, $handlerId);
        $reflectionClass = $container->getReflectionClass($handlerClass);

        if ($reflectionClass === null) {
            throw new \Siemieniec\AsyncCommandBus\DependencyInjection\RuntimeException(
                \sprintf('Could not create ReflectionClass of %s during  compiler pass', $handlerClass)
            );
        }

        if (!$reflectionClass->hasMethod('__invoke')) {
            throw new \Siemieniec\AsyncCommandBus\DependencyInjection\HandlerRegistryException(
                \sprintf('__invoke method not implemented in %s', $handlerClass)
            );
        }

        $method = $reflectionClass->getMethod('__invoke');

        if (\count($method->getParameters()) !== 1) {
            throw new \Siemieniec\AsyncCommandBus\DependencyInjection\HandlerRegistryException(
                \sprintf(
                    'Invalid number of parameters of __invoke method in class %s. Expected 1 got %d',
                    $handlerClass,
                    \count($method->getParameters())
                )
            );
        }

        $param = $method->getParameters()[0]->getType();

        if ($param === null) {
            throw new \Siemieniec\AsyncCommandBus\DependencyInjection\HandlerRegistryException(
                \sprintf(
                    'Unable to register handler. __invoke method parameter of class %s is missing type.',
                    $handlerClass
                )
            );
        }

        if (!($param instanceof ReflectionNamedType)) {
            throw new \Siemieniec\AsyncCommandBus\DependencyInjection\HandlerRegistryException(
                \sprintf(
                    'Unable to register handler. Got invalid reflection parameter type of %s',
                    \get_class($param)
                )
            );
        }

        return $param->getName();
    }
}
