<?php

namespace Mshavliuk\SignalEventsBundle\Tests;

use Mshavliuk\SignalEventsBundle\SignalEventsBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SignalEventsBundleTest extends TestCase
{
    public function testBundleWillCreateExtension(): void
    {
        $bundle = new SignalEventsBundle();
        $this->assertInstanceOf(Extension::class, $bundle->getContainerExtension());
    }
}
