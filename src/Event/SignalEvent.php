<?php

namespace Mshavliuk\SignalEventsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class SignalEvent extends Event
{
    public const NAME = 'signal.handled';

    protected $signal;
    protected $signalInfo;

    /**
     * @param int $signal
     * @param mixed $signalInfo
     */
    public function __construct(int $signal, $signalInfo = null)
    {
        $this->signal = $signal;
        $this->signalInfo = $signalInfo;
    }

    /**
     * @return int
     */
    public function getSignal(): int
    {
        return $this->signal;
    }

    /**
     * @return mixed
     */
    public function getSignalInfo()
    {
        return $this->signalInfo;
    }
}
