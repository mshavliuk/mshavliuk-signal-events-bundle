<?php

namespace Mshavliuk\SignalEventsBundle\DependencyInjection;

use Exception;
use Mshavliuk\SignalEventsBundle\EventListener\ServiceStartupListener;
use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SignalEventsExtension extends Extension
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
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');

        $serviceDefinition = $this->defineHandleService($container);
        if ($container->hasParameter('signal_events.start_at')) {
            $this->defineEventListener($container, $container->getParameter('signal_events.start_at'));
            $serviceDefinition->addMethodCall('addObservableSignals', ['%signal_events.handle_signals%']);
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return Definition
     */
    protected function defineHandleService(ContainerBuilder $container): Definition
    {
        $definition = new Definition(SignalHandlerService::class);
        $definition->setPublic(true);
        $definition->setAutowired(true);
        $container->setDefinition(SignalHandlerService::class, $definition);
        $container->setAlias('signal_events.handle_service', SignalHandlerService::class);

        return $definition;
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
        return 'signal_events';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }
}
