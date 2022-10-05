<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Cli\DebugCommandsConfigCommand;
use App\Command\CommandInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DebugConfigCommandCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $commands = [];
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->getClass() && \is_subclass_of($definition->getClass(), CommandInterface::class)) {
                $commands[] = $definition->getClass();
            }
        }

        $container->findDefinition(DebugCommandsConfigCommand::class)->setArgument('$commands', $commands);
    }
}
