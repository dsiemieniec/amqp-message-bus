<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus;

use Exception;
use Siemieniec\AmqpMessageBus\DependencyInjection\HandlerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\DependencyInjection\MessageSerializerRegistryCompilerPass;
use Siemieniec\AmqpMessageBus\Serializer\MessageSerializerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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

        $rootNode = $definition->rootNode();
        $this->addGlobals($rootNode);
        $this->addConnections($rootNode);
        $this->addExchanges($rootNode);
        $this->addQueues($rootNode);
        $this->addMessages($rootNode);
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

    private function addGlobals(NodeDefinition|ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->booleanNode('auto_declare')
                    ->defaultFalse()
                ->end()
            ->end()
        ;
    }

    private function addConnections(NodeDefinition|ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('connections')
                    ->isRequired()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('user')->end()
                            ->scalarNode('password')->end()
                            ->scalarNode('vhost')->defaultValue('/')->end()
                            ->booleanNode('keep_alive')->end()
                            ->scalarNode('heartbeat')->end()
                            ->booleanNode('insist')->end()
                            ->scalarNode('login_method')->end()
                            ->scalarNode('locale')->end()
                            ->scalarNode('connection_timeout')->end()
                            ->scalarNode('read_write_timeout')->end()
                            ->booleanNode('keep_alive')->end()
                            ->booleanNode('ssl_protocol')->end()
                            ->arrayNode('nodes')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                        ->scalarNode('user')->end()
                                        ->scalarNode('password')->end()
                                        ->scalarNode('vhost')->defaultValue('/')->end()
                                        ->booleanNode('keep_alive')->end()
                                        ->scalarNode('heartbeat')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addExchanges(NodeDefinition|ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('exchanges')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('type')->end()
                        ->scalarNode('connection')->end()
                        ->booleanNode('passive')->end()
                        ->booleanNode('durable')->end()
                        ->booleanNode('auto_delete')->end()
                        ->booleanNode('internal')->end()
                        ->booleanNode('auto_declare')->end()
                        ->variableNode('arguments')->end()
                        ->arrayNode('queue_bindings')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('queue')->isRequired()->end()
                                    ->scalarNode('routing_key')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addQueues(ArrayNodeDefinition|NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('queues')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('connection')->end()
                            ->booleanNode('passive')->end()
                            ->booleanNode('durable')->end()
                            ->booleanNode('exclusive')->end()
                            ->booleanNode('auto_delete')->end()
                            ->booleanNode('auto_declare')->end()
                            ->variableNode('arguments')->end()
                            ->arrayNode('consumer')
                                ->children()
                                    ->scalarNode('tag')->end()
                                    ->booleanNode('ack')->end()
                                    ->booleanNode('exclusive')->end()
                                    ->booleanNode('local')->end()
                                    ->scalarNode('prefetch_count')->end()
                                    ->scalarNode('time_limit')->end()
                                    ->scalarNode('wait_timeout')->end()
                                    ->scalarNode('messages_limit')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addMessages(ArrayNodeDefinition|NodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('messages')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('serializer')->end()
                            ->booleanNode('requeue_on_failure')->end()
                            ->arrayNode('publisher')
                                ->children()
                                    ->arrayNode('exchange')
                                        ->children()
                                            ->scalarNode('name')->isRequired()->end()
                                            ->scalarNode('routing_key')->isRequired()->end()
                                        ->end()
                                    ->end()
                                    ->scalarNode('queue')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
