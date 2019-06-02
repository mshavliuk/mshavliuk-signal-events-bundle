<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle\Tests\Service;

use Generator;
use Mshavliuk\MshavliukSignalEventsBundle\Event\SignalEvent;
use Mshavliuk\MshavliukSignalEventsBundle\Service\SignalConstants;
use Mshavliuk\MshavliukSignalEventsBundle\Service\SignalHandlerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
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

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach (SignalConstants::SUPPORTED_SIGNALS as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }
        $this->signalHandlerService->__destruct();
        unset($this->signalHandlerService);
    }

    /**
     * @dataProvider providerSupportedSignals
     *
     * @param string $signalName
     * @param int $signal
     */
    public function testAddObservableSignalsWillAddSignalByName(string $signalName, int $signal): void
    {
        $this->signalHandlerService->addObservableSignals([$signalName]);
        $actualSignals = $this->signalHandlerService->getObservableSignals();
        $this->assertCount(1, $actualSignals);
        $this->assertSame($signal, $actualSignals[0]);
    }

    /**
     * @dataProvider providerSupportedSignals
     *
     * @param string $signalName
     * @param int $signal
     */
    public function testAddObservableSignalsWillAddSignalByCode(string $signalName, int $signal): void
    {
        $this->signalHandlerService->addObservableSignals([$signal]);
        $actualSignals = $this->signalHandlerService->getObservableSignals();
        $this->assertCount(1, $actualSignals);
        $this->assertSame($signal, $actualSignals[0]);
    }

    /**
     * @dataProvider providerSignalChunks
     *
     * @param array $signalChunk
     * @param array $expectedValues
     */
    public function testAddObservableSignalsWillAddSignalChunk(array $signalChunk, array $expectedValues): void
    {
        $this->signalHandlerService->addObservableSignals($signalChunk);
        $actualSignals = $this->signalHandlerService->getObservableSignals();

        $this->assertEmpty(array_diff($actualSignals, $expectedValues));
    }

    public function testSignalHandlerWillThrowErrorIfUnknownSignal(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/\w+ is not supported signal/');
        $this->signalHandlerService->addObservableSignals(['UNKNOWN_SIGNAL']);
    }

    /**
     * @dataProvider providerUnsupportedSignals
     *
     * @param string $signalName
     * @param int $signal
     */
    public function testSignalHandlerWillThrowErrorIfUnsupportedSignal(string $signalName, int $signal): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/\w+ is not supported signal/');
        $this->signalHandlerService->addObservableSignals([$signal]);
    }

    /**
     * @dataProvider providerRemovedSignals
     *
     * @param array<string|int> $initSignals
     * @param array<string|int> $removeSignals
     */
    public function testSignalHandlerWillRemoveSignal($initSignals, $removeSignals): void
    {
        $this->signalHandlerService->addObservableSignals($initSignals);
        foreach ($removeSignals as $removeSignal) {
            $this->signalHandlerService->removeObservableSignal($removeSignal);
        }
        $actualSignals = $this->signalHandlerService->getObservableSignals();
        $expectRemovedSignals = array_intersect($initSignals, $removeSignals);
        $this->assertEmpty(array_diff($actualSignals, array_diff($actualSignals, $expectRemovedSignals)));
    }

    /**
     * @dataProvider providerSupportedSignals
     *
     * @param string $signalName
     * @param int $signal
     */
    public function testSignalHandlerHandleSignal(string $signalName, int $signal): void
    {
        $this->dispatcher->expects($spy = $this->once())->method('dispatch');
        $this->signalHandlerService->addObservableSignals([$signalName]);

        posix_kill(posix_getpid(), $signal);

        [$event, $eventName] = $spy->getInvocations()[0]->getParameters();
        /* @var SignalEvent $event */
        $this->assertEquals($event->getSignal(), $signal);
        $this->assertEquals($eventName, SignalEvent::NAME);
    }

    /**
     * @dataProvider providerSupportedSignals
     *
     * @param string $signalName
     * @param int $signal
     */
    public function testSignalHandlerWillAddSignalInformationInEvent(string $signalName, int $signal): void
    {
        $this->dispatcher->expects($spy = $this->once())->method('dispatch');
        $this->signalHandlerService->addObservableSignals([$signalName]);

        posix_kill(posix_getpid(), $signal);
        [$event, ] = $spy->getInvocations()[0]->getParameters();
        /* @var SignalEvent $event */
        $signalInfo = $event->getSignalInfo();
        $this->assertNotEmpty($signalInfo);
        $this->assertSame($signal, $signalInfo['signo']);
    }

    /**
     * @dataProvider providerRemovedSignals
     *
     * @param array<string|int> $initSignals
     * @param array<string|int> $removeSignals
     */
    public function testSignalHandlerWillRestoreSignalHandlerAfterRemove($initSignals, $removeSignals): void
    {
        $this->dispatcher->expects($spy = $this->never())->method('dispatch');
        $this->signalHandlerService->addObservableSignals((array) $initSignals);
        foreach ($removeSignals as $removeSignal) {
            $this->signalHandlerService->removeObservableSignal($removeSignal);
        }
        $expectRemovedSignals = array_intersect($initSignals, $removeSignals);
        foreach ($expectRemovedSignals as $signal) {
            $signal = is_string($signal) ? constant($signal) : $signal;
            $actualHandler = pcntl_signal_get_handler($signal);
            $this->assertSame(SIG_DFL, $actualHandler);
        }
    }

    // TODO: SignalHandlerWillStopListenSignalsAfterDisable
    // TODO: SignalHandlerWillStartListenSignalsAfterEnable

    public function providerUnsupportedSignals(): Generator
    {
        foreach (SignalConstants::UNSUPPORTED_SIGNALS as $signalName => $signal) {
            yield $signalName => [$signalName, $signal];
        }
    }

    public function providerSupportedSignals(): Generator
    {
        foreach (SignalConstants::SUPPORTED_SIGNALS as $signalName => $signal) {
            yield $signalName => [$signalName, $signal];
        }
    }

    public function providerSignalChunks(): array
    {
        return [
            'string signals' => [['SIGPIPE', 'SIGBUS'], [SIGPIPE, SIGBUS]],
            'mixed signals' => [[SIGPIPE, 'SIGBUS'], [SIGPIPE, SIGBUS]],
            'single signal 0' => [[SIGTRAP], [SIGTRAP]],
            'single signal 1' => [[SIGHUP], [SIGHUP]],
            'single signal 2' => [[SIGPIPE], [SIGPIPE]],
            'single signal 3' => [[SIGILL], [SIGILL]],
            'few signals' => [[SIGTRAP, SIGINT, SIGBUS, SIGHUP], [SIGTRAP, SIGINT, SIGBUS, SIGHUP]],
            'repeated signals' => [[SIGTRAP, SIGTRAP, SIGBUS, SIGBUS], [SIGTRAP, SIGBUS]],
            'empty chunk' => [[], []],
        ];
    }

    public function providerRemovedSignals(): array
    {
        return [
            'single string' => [['SIGTRAP'], ['SIGTRAP']],
            'single int' => [['SIGTRAP'], [SIGTRAP]],
            'overlap' => [['SIGHUP', SIGTRAP], [SIGHUP, SIGTRAP, SIGINT]],
            'repeated' => [[SIGTRAP], [SIGTRAP, SIGTRAP, SIGTRAP]],
            'remove from empty' => [[], [SIGTRAP]],
            'remove empty' => [[SIGTRAP], []],
            'remove many' => [SignalConstants::SUPPORTED_SIGNALS, SignalConstants::SUPPORTED_SIGNALS],
            'remove another' => [SignalConstants::SUPPORTED_SIGNALS, SignalConstants::UNSUPPORTED_SIGNALS],
        ];
    }
}
