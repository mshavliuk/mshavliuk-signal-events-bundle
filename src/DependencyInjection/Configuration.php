<?php

namespace Mshavliuk\SignalEventsBundle\DependencyInjection;

use function method_exists;
use RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $builder = new TreeBuilder('signal_events');

        if (method_exists($builder, 'getRootNode')) {
            $root = $builder->getRootNode();
        } else {
            // symfony < 4.2 support
            $root = $builder->root('signal_events');
        }

        $root
            ->children()
                ->booleanNode('logging')->defaultValue('%signal_events.logging%')->end()
                ->arrayNode('handle_signals')->defaultValue('%signal_events.handle_signals%')->end()
            ->end();

        return $builder;
    }
}
