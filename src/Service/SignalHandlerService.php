<?php

declare(strict_types=1);

namespace Mshavliuk\SignalEventsBundle\Service;

use Closure;
use Mshavliuk\SignalEventsBundle\Event\SignalEvent;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignalHandlerService
{
    public const SUPPORTED_SIGNALS = [
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
        'SIGKILL' => SIGKILL,
        'SIGSTOP' => SIGSTOP,
    ];

    /** @var EventDispatcherInterface|EventDispatcher */
    protected $dispatcher;
    /** @var array */
    protected $observableSignals;
    /** @var Closure */
    private $signalHandler;

    /**
     * SignalHandlerService constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->signalHandler = $this->makeSignalHandler();
        $this->enable();
    }

    public function addObservableSignals(array $signals): self
    {
        foreach ($signals as $signal) {
            $signalCode = $this->getSignalCode($signal);
            if (!pcntl_signal($signalCode, $this->signalHandler)) {
                throw new RuntimeException('Cannot set signal handler');
            }
            $this->observableSignals[] = $signalCode;
        }

        return $this;
    }

    public function removeObservableSignal($signal): self
    {
        $signalCode = $this->getSignalCode($signal);
        if (false !== ($key = array_search($signalCode, $this->observableSignals, true))) {
            unset($this->observableSignals[$key]);      // TODO: test is array don't have any holes
            pcntl_signal($signalCode, SIG_DFL);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getObservableSignals(): array
    {
        return $this->observableSignals;
    }

    private function getSignalCode($signal): int
    {
        $signalCode = null;
        if (is_int($signal) && in_array($signal, self::SUPPORTED_SIGNALS, true)) {
            $signalCode = $signal;
        } elseif (is_string($signal) && isset(self::SUPPORTED_SIGNALS[$signal])) {
            $signalCode = self::SUPPORTED_SIGNALS[$signal];
        }

        if (null === $signalCode) {
            throw new RuntimeException("$signal is not supported signal");
        }

        return $signalCode;
    }

    private function makeSignalHandler(): callable
    {
        return function (int $signal, $signalInfo = null) {
            $event = new SignalEvent($signal, $signalInfo);
            $this->dispatcher->dispatch($event, SignalEvent::NAME);
        };
    }

    public function enable()
    {
        pcntl_async_signals(true);
    }

    public function disable()
    {
        pcntl_async_signals(false);
    }

    public function __destruct()
    {
        $this->disable();
    }
}
