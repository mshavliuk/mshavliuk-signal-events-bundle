<?php

namespace Mshavliuk\SignalEventsBundle\DependencyInjection;

use Exception;
use Mshavliuk\SignalEventsBundle\EventListener\ServiceStartupListener;
use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use Symfony\Component\Console\ConsoleEvents;
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
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $logging       = $config['logging'] === true;

        $this->defineHandleService($container);
        $this->defineEventListener($container);
    }


    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    protected function defineHandleService(ContainerBuilder $container)
    {
        $serviceDefinition = new Definition(SignalHandlerService::class);
        $serviceDefinition->setArguments(['$signals' => '%signal_events.handle_signals%']);
        $serviceDefinition->setPublic(true);
        $serviceDefinition->setAutowired(true);
        $container->setDefinition(SignalHandlerService::class, $serviceDefinition);
        $container->setAlias('signal_events.handle_service', SignalHandlerService::class);
    }

    protected function defineEventListener(ContainerBuilder $container)
    {
        $serviceDefinition = new Definition(ServiceStartupListener::class);
        $serviceDefinition->setAutowired(true);
        $serviceDefinition->addTag('kernel.event_listener', ['event' => ConsoleEvents::COMMAND, 'method' => 'handleStartupEvent']);
        $container->setDefinition(ServiceStartupListener::class, $serviceDefinition);
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'signal_events';
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }
}
