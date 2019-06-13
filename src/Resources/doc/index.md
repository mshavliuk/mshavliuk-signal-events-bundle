# MshavliukSignalEventsBundle #

## About ##

This bundle provides service, that emits events for every handled UNIX signals into your Symfony application. It relies
on PCNTL php-extension, which you can check by running
```bash
$ php --ri pcntl
```

Expected output:
```
pcntl
pcntl support => enabled
```

## Installation ##

Add the `mshavliuk/mshavliuk-signal-events-bundle` package to your `require` section in the `composer.json` file.

```bash
$ composer require mshavliuk/mshavliuk-signal-events-bundle
```

## Usage ##

### Automatic startup ###

By default it will handle every possible signals and startup after `console.command` event which is basically in every
`php bin/console` run.


Configure the bundle in your `config.yml`:

```yaml
mshavliuk_signal_events:
    startup_events:
        - console.command       # to handle signals while console commands (default)
        - kernel.request        # to handle signals while requests processing
    handle_signals:
        - SIGINT                # ctrl+c
        - SIGTSTP               # ctrl+z
```

### Manual ###

To prevent automatic startup you can specify empty arrays for `startup_events` config:

```yaml
mshavliuk_signal_events:
    startup_events: []      # prevent startup
```

In that case you can manually inject `Mshavliuk\MshavliukSignalEventsBundle\Service\SignalHandlerService` and configure
signals to handle:

```php
public function __construct(SignalHandlerService $service)
{
    $service->addObservableSignals(['SIGINT', 'SIGHUP']);
}
```

### Handling signal events

You can register any callback function to handle specific event via `EventDispatcherInterface::addListener` function.
See the following example:

```php
$eventDispatcher->addListener(SignalEvent::NAME, function($event, $eventName) use ($output) {
    if($event->getSignal() === SIGINT) {
        $output->writeln('Ctrl+C signal handled');
    }
});

```

Also you can create special listener class and bind its public method for process any signals:

services.yaml:
```yaml
App\EventListener\SignalListener:
    tags:
        - { name: kernel.event_listener, event: signal.handled, method: onSignal }
```

SignalListener.php:
```php
class SignalListener
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public function onSignal($event)
    {
        $this->logger->info('handle signal event', ['event' => $event]);
    }
}
```

For more information read the official Symfony [documentation](https://symfony.com/doc/current/event_dispatcher.html).

## License ##

See [LICENSE](LICENSE).
