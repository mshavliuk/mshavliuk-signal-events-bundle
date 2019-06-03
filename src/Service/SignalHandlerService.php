<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle\Service;

use Mshavliuk\MshavliukSignalEventsBundle\Event\SignalEvent;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignalHandlerService
{
    /** @var EventDispatcherInterface|EventDispatcher */
    protected $dispatcher;
    /** @var array */
    protected $observableSignals = [];
    /** @var callable */
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

    /**
     * @param int|string $signal
     *
     * @return SignalHandlerService
     */
    public function removeObservableSignal($signal): self
    {
        try {
            $signalCode = $this->getSignalCode($signal);
        } catch (RuntimeException $e) {
            return $this;
        }

        if (false !== ($key = array_search($signalCode, $this->observableSignals, true))) {
            unset($this->observableSignals[$key]);
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

    /**
     * @param int|string $signal
     *
     * @return int
     */
    private function getSignalCode($signal): int
    {
        $signalCode = null;
        if (is_int($signal)
            && $signal >= SignalConstants::MIN_VALID_SIGNAL && $signal <= SignalConstants::MAX_VALID_SIGNAL
            && !in_array($signal, SignalConstants::UNSUPPORTED_SIGNALS, true)) {
            $signalCode = $signal;
        } elseif (is_string($signal) && isset(SignalConstants::SUPPORTED_SIGNALS[$signal])) {
            $signalCode = SignalConstants::SUPPORTED_SIGNALS[$signal];
        }

        if (null === $signalCode) {
            throw new RuntimeException("$signal is not supported signal");
        }

        return $signalCode;
    }

    /**
     * @return callable
     */
    private function makeSignalHandler(): callable
    {
        return function (int $signal, $signalInfo = null) {
            $event = new SignalEvent($signal, $signalInfo);
            if (method_exists($this->dispatcher, 'callListeners')) {
                $this->dispatcher->dispatch($event, SignalEvent::NAME);
            } else {
                $this->dispatcher->dispatch(SignalEvent::NAME, $event);
            }
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
