<?php

namespace Mshavliuk\SignalEventsBundle\Service;

interface SignalConstants
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

    public const MIN_VALID_SIGNAL = 1;
    public const MAX_VALID_SIGNAL = 31;

    public const SIGNALS = self::SUPPORTED_SIGNALS + self::UNSUPPORTED_SIGNALS;
}
