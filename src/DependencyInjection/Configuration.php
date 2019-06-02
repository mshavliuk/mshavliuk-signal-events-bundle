<?php

namespace Mshavliuk\MshavliukSignalEventsBundle\DependencyInjection;

use Mshavliuk\MshavliukSignalEventsBundle\Service\SignalConstants;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Console\ConsoleEvents;
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
        $builder = new TreeBuilder('mshavliuk_signal_events');

        if (method_exists($builder, 'getRootNode')) {
            $root = $builder->getRootNode();
        } else {
            // symfony < 4.2 support
            $root = $builder->root('mshavliuk_signal_events');
        }

        /* @var ArrayNodeDefinition $root */
        $root
            ->children()
                ->arrayNode('startup_events')
                    ->defaultValue([
                        ConsoleEvents::COMMAND,
                    ])
                    ->beforeNormalization()
                    ->ifString()
                        ->then(static function ($v) { return [$v]; })
                        ->end()
                    ->prototype('scalar')
                    ->defaultValue('%mshavliuk_signal_events.startup_events%')
                    ->end()
                ->end()
                ->arrayNode('handle_signals')
                    ->defaultValue(SignalConstants::SUPPORTED_SIGNALS)
                    ->beforeNormalization()
                    ->ifString()
                        ->then(static function ($v) { return [$v]; })
                        ->end()
                    ->prototype('scalar')
                    ->end()
                ->end();

        return $builder;
    }
}
