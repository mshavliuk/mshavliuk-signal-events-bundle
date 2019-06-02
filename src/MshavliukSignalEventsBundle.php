<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle;

use Mshavliuk\MshavliukSignalEventsBundle\DependencyInjection\MshavliukSignalEventsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MshavliukSignalEventsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    protected function createContainerExtension()
    {
        return new MshavliukSignalEventsExtension();
    }
}
