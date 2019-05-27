<?php

namespace Mshavliuk\SignalEventsBundle\EventListener;

use Exception;
use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ServiceStartupListener
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected $startupEvents;

    /**
     * @param Event|ConsoleCommandEvent $event
     *
     * @throws Exception
     */
    public function handleStartupEvent($event)
    {
        $this->container->get(SignalHandlerService::class);
    }
}
