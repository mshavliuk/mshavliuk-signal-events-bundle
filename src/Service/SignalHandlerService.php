<?php
declare(strict_types=1);

namespace Mshavliuk\SymfonySignalHandler\Service;

class SignalHandlerService
{
    public const SIGNALS = [
        'SIG_IGN' => SIG_IGN,
        'SIG_DFL' => SIG_DFL,
        'SIG_ERR' => SIG_ERR,
        'SIGHUP' => SIGHUP,
        'SIGINT' => SIGINT,
        'SIGQUIT' => SIGQUIT,
        'SIGILL' => SIGILL,
        'SIGTRAP' => SIGTRAP,
        'SIGABRT' => SIGABRT,
        'SIGIOT' => SIGIOT,
        'SIGBUS' => SIGBUS,
        'SIGFPE' => SIGFPE,
        'SIGKILL' => SIGKILL,
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
        'SIGSTOP' => SIGSTOP,
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
    }


    public function setSignalHandler(callable $function, array $signals = [SIGINT, SIGTERM, SIGHUP]): int
    {
        pcntl_async_signals(true);

        foreach ($signals as $signal) {
            pcntl_signal($signal, $function);
        }

        return 0; // TODO: return handler id
    }


    public function removeSignalHandler(int $handlerId)
    {
        pcntl_async_signals(false);
        // TODO: remove signal handler
    }
}
