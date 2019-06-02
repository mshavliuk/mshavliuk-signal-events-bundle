<?php

namespace Mshavliuk\MshavliukSignalEventsBundle\Tests;

use Mshavliuk\MshavliukSignalEventsBundle\MshavliukSignalEventsBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class MshavliukSignalEventsBundleTest extends TestCase
{
    public function testBundleWillCreateExtension(): void
    {
        $bundle = new MshavliukSignalEventsBundle();
        $this->assertInstanceOf(Extension::class, $bundle->getContainerExtension());
    }
}
