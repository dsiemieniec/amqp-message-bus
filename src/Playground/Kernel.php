<?php

declare(strict_types=1);

namespace App;

use Siemieniec\AmqpMessageBus\DependencyInjection\MessageSerializerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\DependencyInjection\HandlerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\Serializer\MessageSerializerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const APP_MESSAGE_SERIALIZER_TAG = 'app.message_serializer';

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(MessageSerializerInterface::class)
            ->addTag(self::APP_MESSAGE_SERIALIZER_TAG);

        $container->addCompilerPass(new HandlerRegistryCompilerPass());
        $container->addCompilerPass(new MessageSerializerRegistryCompilerPass());
    }
}
