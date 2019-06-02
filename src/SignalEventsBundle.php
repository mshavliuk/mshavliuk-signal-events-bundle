<?php

namespace Mshavliuk\SignalEventsBundle;

use Mshavliuk\SignalEventsBundle\DependencyInjection\SignalEventsExtension;
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
