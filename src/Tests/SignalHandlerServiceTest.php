<?php

declare(strict_types=1);

use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class SignalHandlerServiceTest extends TestCase
{
    public function testConstructor()
    {
        $handler = new SignalHandlerService();
        $this->assertInstanceOf(SignalHandlerService::class, $handler);
    }

    /**
     * @dataProvider providerSignalNames
     *
     * @param $signalName
     * @param $signal
     */
    public function testSignalHandlerHandleSignal($signalName, $signal)
    {
        $handler = new SignalHandlerService();
        $handledSignal = null;
        $handler->addSignalHandler(
            static function ($signal, $signalInfo = null) use (&$handledSignal) {
                $handledSignal = $signal;
            }
        );
        $process = new Process(['kill', "-$signalName", posix_getpid()]);
        $process->run();
        $this->assertEquals($handledSignal, $signal);
    }

    public function providerSignalNames()
    {
        foreach (SignalHandlerService::SUPPORTED_SIGNALS as $signalName => $signal) {
            yield $signalName => [$signalName, $signal];
        }
    }
}
