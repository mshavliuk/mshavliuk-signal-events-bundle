<?php

declare(strict_types=1);

namespace Mshavliuk\SignalEventsBundle\Tests\Service;

use Generator;
use Mshavliuk\SignalEventsBundle\Event\SignalEvent;
use Mshavliuk\SignalEventsBundle\Service\SignalConstants;
use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SignalHandlerServiceTest extends TestCase
{
    /** @var SignalHandlerService */
    protected $signalHandlerService;
    /** @var EventDispatcher&MockObject */
    protected $dispatcher;

    protected function setUp(): void
    {
        /* @var EventDispatcher $dispatcher */
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->signalHandlerService = new SignalHandlerService($this->dispatcher);
    }

    /**
     * @dataProvider providerSupportedSignals
     *
     * @param string $signalName
     * @param int $signal
     */
    public function testSignalHandlerHandleSignal(string $signalName, int $signal)
    {
        $this->dispatcher->expects($spy = $this->once())->method('dispatch');
        $this->signalHandlerService->addObservableSignals([$signalName]);

        posix_kill(posix_getpid(), $signal);

        [$event, $eventName] = $spy->getInvocations()[0]->getParameters();
        /* @var SignalEvent $event */
        $this->assertEquals($event->getSignal(), $signal);
        $this->assertEquals($eventName, SignalEvent::NAME);
    }

    public function providerSupportedSignals(): Generator
    {
        foreach (SignalConstants::SUPPORTED_SIGNALS as $signalName => $signal) {
            yield $signalName => [$signalName, $signal];
        }
    }
}
