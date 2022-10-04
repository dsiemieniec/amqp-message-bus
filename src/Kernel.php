<?php

namespace App;

use App\DependencyInjection\HandlerRegistryCompilerPass;
use App\Handler\HandlerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const APP_COMMAND_HANDLER_TAG = 'app.command_handler';

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag(self::APP_COMMAND_HANDLER_TAG);

        $container->addCompilerPass(new HandlerRegistryCompilerPass());
    }
}
