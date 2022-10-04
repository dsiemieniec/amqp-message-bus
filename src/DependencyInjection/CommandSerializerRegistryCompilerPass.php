<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Kernel;
use App\Serializer\CommandSerializerRegistry;
use RuntimeException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandSerializerRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $commandSerializerRegistry = $container->findDefinition(CommandSerializerRegistry::class);
        $serializerIds = $container->findTaggedServiceIds(Kernel::APP_COMMAND_SERIALIZER_TAG);
        foreach ($serializerIds as $serializerId => $tags) {
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

            $serializerClass = $definition->getClass();
            if ($serializerClass === null) {
                throw new RuntimeException(
                    \sprintf('Could not establish class of %s during compiler pass', $serializerId)
                );
            }

            return $serializerClass;
        }
    }
}
