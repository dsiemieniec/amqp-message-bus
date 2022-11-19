<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus;

use Siemieniec\AmqpMessageBus\DependencyInjection\HandlerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\DependencyInjection\MessageSerializerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\Serializer\MessageSerializerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AmqpMessageBus extends AbstractBundle
{
    public const APP_MESSAGE_SERIALIZER_TAG = 'app.message_serializer';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(MessageSerializerInterface::class)
            ->addTag(self::APP_MESSAGE_SERIALIZER_TAG);

        $container->addCompilerPass(new HandlerRegistryCompilerPass());
        $container->addCompilerPass(new MessageSerializerRegistryCompilerPass());
    }
}
