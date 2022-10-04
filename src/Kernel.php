<?php

namespace App;

use App\DependencyInjection\CommandSerializerRegistryCompilerPass;
use App\DependencyInjection\HandlerRegistryCompilerPass;
use App\Handler\HandlerInterface;
use App\Serializer\CommandSerializerInterface;
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
