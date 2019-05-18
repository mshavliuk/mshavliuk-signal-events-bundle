<?php
declare(strict_types=1);


use Mshavliuk\SymfonySignalHandler\Service\SignalHandlerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class SignalHandlerServiceTest extends TestCase
{
    public const SIGNALS = [
//        'SIG_IGN',
//        'SIG_DFL',
//        'SIG_ERR',
//        'SIGHUP',
//        'SIGINT',
        'SIGQUIT', //Quit
        'SIGILL', //Illegal instruction
        'SIGTRAP', //Trace/breakpoint trap
        'SIGABRT', //Aborted
        'SIGIOT', //Aborted
        'SIGBUS', //Bus error
        'SIGFPE', //Floating point exception
        'SIGKILL', //Killed
        'SIGUSR1',//User defined signal 1
        'SIGSEGV',//Segmentation fault
        'SIGUSR2',
        'SIGPIPE',
        'SIGALRM',
        'SIGTERM',
        'SIGSTKFLT',
        'SIGCLD',
        'SIGCHLD',
        'SIGCONT',
        'SIGSTOP',
        'SIGTSTP',
        'SIGTTIN',
        'SIGTTOU',
        'SIGURG',
        'SIGXCPU',
        'SIGXFSZ',
        'SIGVTALRM',
        'SIGPROF',
        'SIGWINCH',
        'SIGPOLL',
        'SIGIO',
        'SIGPWR',
        'SIGSYS',
        'SIGBABY',
    ];

    public const UNHANDLED_SIGNALS = [
        'SIGQUIT',
        'SIGILL',
        'SIGTRAP',
        'SIGABRT',
        'SIGIOT',
        'SIGBUS',
        'SIGFPE',
        'SIGKILL',
        'SIGUSR1',
        'SIGSEGV',
    ];

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
        $handler->setSignalHandler(
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
        foreach (self::SIGNALS as $signal) {
            if(!in_array($signal, self::UNHANDLED_SIGNALS, true)) {
                yield $signal => [$signal, constant($signal)];
            }
        }
    }
}