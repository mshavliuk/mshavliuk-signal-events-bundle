<?php
declare(strict_types=1);

namespace Mshavliuk\SignalEventsBundle\Service;

use RuntimeException;

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

    protected const STATE_ENABLED = 'enabled';
    protected const STATE_DISABLED = 'disabled';

    protected $callbacks = [];
    protected $signalCallbacks = [];


    public function __construct()
    {
        pcntl_async_signals(true);
    }


    public function addSignalHandler(
        callable $callback,
        array $signals = [SIGINT, SIGTERM, SIGHUP],
        $throwOnError = true
    ): int {

        $callbackId = count($this->callbacks);
        $this->callbacks[$callbackId] = $callback;
        // TODO: check unsupported signals

        foreach ($signals as $signal) {
            if(!isset($this->callbacks[$signal])) {
                $this->signalCallbacks[$signal] = [];
            }

            $this->signalCallbacks[$signal][] = [
                'id' => $callbackId,
                'state' => self::STATE_ENABLED
            ];
            /** @noinspection NotOptimalIfConditionsInspection */
            if (!pcntl_signal($signal, [$this, 'handleSignal']) && $throwOnError) {
                throw new RuntimeException('Cannot set signal handler');
            }
        }

        return $callbackId;
    }

    /**
     * @param $signal
     * @param null $signalInfo
     *
     * @internal
     */
    public function handleSignal($signal, $signalInfo = null)
    {
        foreach ($this->signalCallbacks[$signal] as ['id' => $callbackId, 'state' => $state]) {
            if($state !== self::STATE_ENABLED) {
                continue;
            }

            $callback = $this->callbacks[$callbackId];
            $callback($signal, $signalInfo);
        }
    }


    public function disableSignalHandler(int $callbackId, $signal = null)
    {
        // TODO: disable all handler by given signal

        if($signal !== null) {
            return $this->disableSignalCallback($signal, $callbackId);
        }

        foreach ($this->signalCallbacks as $signal => $callbacks) {
            $this->disableSignalCallback($signal, $callbackId);
        }
        return true;
    }

    protected function disableSignalCallback($signal, $callbackId)
    {
        $callbacks = $this->signalCallbacks[$signal];
        foreach ($callbacks as $index => ['id' => $id, 'state' => $state]) {
            if($callbackId === $id) {
                $callbacks[$index]['state'] = self::STATE_DISABLED;
            }
        }
        return true;
    }
}
