<?php

namespace Mshavliuk\SignalEventsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function method_exists;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('signal_events');

        if (method_exists($builder, 'getRootNode')) {
            $root = $builder->getRootNode();
        } else {
            // symfony < 4.2 support
            $root = $builder->root('signal_events');
        }

        /* @var ArrayNodeDefinition $root */
        $root
            ->children()
                ->arrayNode('start_at')->beforeNormalization()
                    ->ifString()
                        ->then(static function ($v) { return [$v]; })
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->defaultValue('%signal_events.start_at%')
                    ->end()
                ->end()
                ->arrayNode('handle_signals')->beforeNormalization()
                    ->ifString()
                        ->then(static function ($v) { return [$v]; })
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->defaultValue('%signal_events.handle_signals%')
                    ->end()
                ->end();

        return $builder;
    }
}
