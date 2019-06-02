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