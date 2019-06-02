<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle\DependencyInjection;

use Exception;
use Mshavliuk\MshavliukSignalEventsBundle\EventListener\ServiceStartupListener;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MshavliukSignalEventsExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');

        if ($config['startup_events']) {
            $serviceDefinition = $container->findDefinition('mshavliuk_signal_events.handle_service');
            $this->defineEventListener($container, $config['startup_events']);
            $serviceDefinition->addMethodCall('addObservableSignals', ['$signals' => $config['handle_signals']]);
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }

    protected function defineEventListener(ContainerBuilder $container, $events): Definition
    {
        $definition = new Definition(ServiceStartupListener::class);
        $definition->setAutowired(true);
        foreach ($events as $event) {
            $definition->addTag('kernel.event_listener', ['event' => $event, 'method' => 'handleStartupEvent']);
        }
        $container->setDefinition(ServiceStartupListener::class, $definition);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'mshavliuk_signal_events';
    }
}
