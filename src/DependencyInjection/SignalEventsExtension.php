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

        $this->defineHandleService($container);
        $this->defineEventListener($container);
    }


    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    protected function defineHandleService(ContainerBuilder $container): void
    {
        $definition = new Definition(SignalHandlerService::class);
        $definition->setArguments(['$signals' => '%signal_events.handle_signals%']);
        $definition->setPublic(true);
        $definition->setAutowired(true);
        $container->setDefinition(SignalHandlerService::class, $definition);
        $container->setAlias('signal_events.handle_service', SignalHandlerService::class);
    }

    protected function defineEventListener(ContainerBuilder $container): void
    {
        $definition = new Definition(ServiceStartupListener::class);
        $definition->setAutowired(true);
        foreach ($container->getParameter('signal_events.start_at') as $event) {
            $definition->addTag('kernel.event_listener', ['event' => $event, 'method' => 'handleStartupEvent']);
        }
        $container->setDefinition(ServiceStartupListener::class, $definition);
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
