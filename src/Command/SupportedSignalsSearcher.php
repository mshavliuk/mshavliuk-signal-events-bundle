<?php

namespace Mshavliuk\SignalEventsBundle\Command;

use Mshavliuk\SignalEventsBundle\Service\SignalConstants;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SupportedSignalsSearcher extends Command
{
    public function __construct()
    {
        parent::__construct('supported-signals');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $supportedSignals = [];
        $phpVersion = PHP_VERSION;
        $output->writeln('php version: '.$phpVersion);
        foreach (SignalConstants::SIGNALS as $signalName => $signal) {
            $process = new Process(['php', dirname(__DIR__).'/../bin/simpleSignalHandler', $signalName]);
            $process->start();
            $process->setTimeout(100);
            try {
                $process->waitUntil(
                    static function ($type, $data) {
                        return 'out' === $type && 'ready' === trim($data);
                    });
                if ($process->isRunning()) {
                    $process->signal($signal);
                    $process->wait();
                } else {
                    $output->writeln("$signalName: fail (process died while trying to start)");
                    continue;
                }
            } catch (RuntimeException $e) {
                $output->writeln("$signalName: fail (".$e->getMessage().')');
                continue;
            } finally {
                if ($process->isRunning()) {
                    $output->writeln('process stay running after signal');
                    $process->signal(SIGKILL);
                }
            }
            if ($process->isSuccessful()) {
                $output->writeln("$signalName: success (".$process->getExitCodeText().')');
                $supportedSignals[] = $signalName;
            } else {
                $output->writeln("$signalName: fail (process was halted)");
            }
        }

        $fp = fopen(dirname(__DIR__)."/../var/{$phpVersion}_supports.json", 'wb');
        fwrite($fp, json_encode([
            'phpVersion' => $phpVersion,
            'supportedSignals' => $supportedSignals,
        ]));
        fclose($fp);
    }
}
