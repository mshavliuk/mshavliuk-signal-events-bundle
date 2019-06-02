<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle\Tests\EventListener;

use Exception;
use Mshavliuk\MshavliukSignalEventsBundle\EventListener\ServiceStartupListener;
use Mshavliuk\MshavliukSignalEventsBundle\Service\SignalHandlerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ServiceStartupListenerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testListenerWillStartServiceOnce(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->with($this->equalTo(SignalHandlerService::class));
        $listener = new ServiceStartupListener($containerMock);
        $eventMock = $this->createMock(Event::class);
        $listener->handleStartupEvent($eventMock);
        $listener->handleStartupEvent($eventMock);
        $listener->handleStartupEvent($eventMock);
        $listener->handleStartupEvent($eventMock);
    }
}
