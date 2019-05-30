<?php

namespace Mshavliuk\SignalEventsBundle;

use Mshavliuk\SignalEventsBundle\DependencyInjection\SignalEventsExtension;
use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SignalEventsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    protected function createContainerExtension()
    {
        return new SignalEventsExtension();
    }
}
