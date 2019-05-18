<?php
declare(strict_types=1);

namespace Mshavliuk\SymfonySignalHandler\Service;

class SignalHandlerService
{

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
