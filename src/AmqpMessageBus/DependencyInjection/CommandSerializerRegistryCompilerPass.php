<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\DependencyInjection;

use App\Kernel;
use Siemieniec\AmqpMessageBus\Serializer\CommandSerializerRegistry;
use RuntimeException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CommandSerializerRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $commandSerializerRegistry = $container->findDefinition(CommandSerializerRegistry::class);
        $serializerIds = $container->findTaggedServiceIds(Kernel::APP_COMMAND_SERIALIZER_TAG);
        foreach (\array_keys($serializerIds) as $serializerId) {
            $commandSerializerRegistry
                ->addMethodCall('registerSerializer', [
                    $this->getSerializerClass($container, $serializerId),
                    new Reference($serializerId)
                ]);
        }
    }

    private function getSerializerClass(ContainerBuilder $container, string $serializerId): string
    {
        while (true) {
            $definition = $container->findDefinition($serializerId);

            if (!$definition->getClass() && $definition instanceof ChildDefinition) {
                $serializerId = $definition->getParent();

                continue;
            }

            return $this->establishSerializerClass($definition, $serializerId);
        }
    }

    private function establishSerializerClass(Definition $definition, string $serializerId): string
    {
        $serializerClass = $definition->getClass();
        if ($serializerClass === null) {
            throw new RuntimeException(
                \sprintf('Could not establish class of %s during compiler pass', $serializerId)
            );
        }

        return $serializerClass;
    }
}
