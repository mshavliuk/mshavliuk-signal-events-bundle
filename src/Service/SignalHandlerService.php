<?php

declare(strict_types=1);

namespace Mshavliuk\SignalEventsBundle\Service;

use Mshavliuk\SignalEventsBundle\Event\SignalEvent;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignalHandlerService
{
    public const SUPPORTED_SIGNALS = [
        'SIG_IGN' => SIG_IGN,
        'SIGHUP' => SIGHUP,
        'SIGINT' => SIGINT,
        'SIGQUIT' => SIGQUIT,
        'SIGILL' => SIGILL,
        'SIGTRAP' => SIGTRAP,
        'SIGABRT' => SIGABRT,
        'SIGIOT' => SIGIOT,
        'SIGBUS' => SIGBUS,
        'SIGFPE' => SIGFPE,
        'SIGUSR1' => SIGUSR1,
        'SIGSEGV' => SIGSEGV,
        'SIGUSR2' => SIGUSR2,
        'SIGPIPE' => SIGPIPE,
        'SIGALRM' => SIGALRM,
        'SIGTERM' => SIGTERM,
        'SIGSTKFLT' => SIGSTKFLT,
        'SIGCLD' => SIGCLD,
        'SIGCHLD' => SIGCHLD,
        'SIGCONT' => SIGCONT,
        'SIGTSTP' => SIGTSTP,
        'SIGTTIN' => SIGTTIN,
        'SIGTTOU' => SIGTTOU,
        'SIGURG' => SIGURG,
        'SIGXCPU' => SIGXCPU,
        'SIGXFSZ' => SIGXFSZ,
        'SIGVTALRM' => SIGVTALRM,
        'SIGPROF' => SIGPROF,
        'SIGWINCH' => SIGWINCH,
        'SIGPOLL' => SIGPOLL,
        'SIGIO' => SIGIO,
        'SIGPWR' => SIGPWR,
        'SIGSYS' => SIGSYS,
        'SIGBABY' => SIGBABY,
    ];

    public const UNSUPPORTED_SIGNALS = [
        'SIG_DFL' => SIG_DFL,
        'SIG_ERR' => SIG_ERR,
        'SIGKILL' => SIGKILL,
        'SIGSTOP' => SIGSTOP,
    ];

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    protected $handledSignals;

    public function __construct(EventDispatcherInterface $dispatcher, $signals)
    {
        pcntl_async_signals(true);

        $this->dispatcher = $dispatcher;

        $this->handledSignals = $signals;

        foreach ($signals as $signal) {
            if (!pcntl_signal($signal, [$this, 'handleSignal'])) {
                throw new RuntimeException('Cannot set signal handler');
            }
        }
    }

    /**
     * @param $signal
     * @param null $signalInfo
     *
     * @internal
     */
    public function handleSignal($signal, $signalInfo = null)
    {
        $event = new SignalEvent($signal, $signalInfo);
        $this->dispatcher->dispatch($event, SignalEvent::NAME);
    }

    /**
     * @return mixed
     */
    public function getHandledSignals()
    {
        return $this->handledSignals;
    }

    public function __destruct()
    {
        pcntl_async_signals(false);
    }
}
