<?php

namespace Mshavliuk\SignalEventsBundle\DependencyInjection;

use Mshavliuk\SignalEventsBundle\Service\SignalConstants;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function method_exists;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\HttpKernel\KernelEvents;

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
                ->arrayNode('startup_events')
                    ->defaultValue([
                        ConsoleEvents::COMMAND,
                        KernelEvents::REQUEST,
                    ])
                    ->beforeNormalization()
                    ->ifString()
                        ->then(static function ($v) { return [$v]; })
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->defaultValue('%signal_events.startup_events%')
                    ->end()
                ->end()
                ->arrayNode('handle_signals')
                    ->defaultValue(SignalConstants::SUPPORTED_SIGNALS)
                    ->beforeNormalization()
                    ->ifString()
                        ->then(static function ($v) { return [$v]; })
                    ->end()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->end()
                ->end();

        return $builder;
    }
}
