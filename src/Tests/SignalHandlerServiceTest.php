<?php

declare(strict_types=1);

namespace Mshavliuk\SignalEventsBundle\Tests;

use Mshavliuk\SignalEventsBundle\Service\SignalHandlerService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

class SignalHandlerServiceTest extends KernelTestCase
{
    /** @var SignalHandlerService */
    protected $signalHandlerService;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->signalHandlerService = $kernel->getContainer()->get(SignalHandlerService::class);
    }


    /**
     * @dataProvider providerSignalNames
     *
     * @param string $signalName
     * @param int $signal
     */
    public function testSignalHandlerHandleSignal(string $signalName, int $signal)
    {
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
