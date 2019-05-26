<?php


namespace Mshavliuk\SignalEventsBundle\Event;


use Symfony\Contracts\EventDispatcher\Event;

class SignalEvent extends Event
{
    public const NAME = 'signal.sent';

    protected $signal;

    public function __construct(int $signal)
    {
        $this->signal = $signal;
    }

    public function getSignal(): int
    {
        return $this->signal;
    }
}