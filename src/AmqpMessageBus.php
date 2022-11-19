<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus;

use Exception;
use Siemieniec\AmqpMessageBus\DependencyInjection\HandlerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\DependencyInjection\MessageSerializerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\Serializer\MessageSerializerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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

    public function configure(DefinitionConfigurator $definition): void
    {
        parent::configure($definition);

        /** @phpstan-ignore-next-line */
        $definition->rootNode()
            ->children()
                ->booleanNode('auto_declare')
                    ->defaultFalse()
                ->end()
                ->arrayNode('connections')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('host')->isRequired()->end()
                            ->scalarNode('port')->isRequired()->end()
                            ->scalarNode('user')->isRequired()->end()
                            ->scalarNode('password')->isRequired()->end()
                            ->scalarNode('vhost')->defaultValue('/')->end()
                            ->booleanNode('keep_alive')->end()
                            ->scalarNode('heartbeat')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param array<int|string, mixed> $config
     * @throws Exception
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('amqp_message_bus', $config);

        $loader = new YamlFileLoader(
            $builder,
            new FileLocator(__DIR__ . '/../config')
        );
        $loader->load('services.yaml');
    }
}
