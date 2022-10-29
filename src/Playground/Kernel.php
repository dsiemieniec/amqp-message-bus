<?php

declare(strict_types=1);

namespace App;

use Siemieniec\AmqpMessageBus\DependencyInjection\CommandSerializerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\DependencyInjection\HandlerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\Handler\HandlerInterface;
use Siemieniec\AmqpMessageBus\Serializer\CommandSerializerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const APP_COMMAND_HANDLER_TAG = 'app.command_handler';
    public const APP_COMMAND_SERIALIZER_TAG = 'app.command_serializer';

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag(self::APP_COMMAND_HANDLER_TAG);
        $container->registerForAutoconfiguration(CommandSerializerInterface::class)
            ->addTag(self::APP_COMMAND_SERIALIZER_TAG);

        $container->addCompilerPass(new HandlerRegistryCompilerPass());
        $container->addCompilerPass(new CommandSerializerRegistryCompilerPass());
    }
}
