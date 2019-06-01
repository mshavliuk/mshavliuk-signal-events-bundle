<?php

namespace Mshavliuk\SignalEventsBundle\Command;

use Mshavliuk\SignalEventsBundle\Service\SignalConstants;
use RuntimeException;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\fclose;
use function Safe\json_encode;

class SupportedSignalsCommand extends Command
{
    public function __construct()
    {
        parent::__construct('supported-signals');
    }

    protected function configure(): void
    {
        $this->setDescription('Check which signals can be handled by php pcntl_signal function')
            ->addOption(
                'signals',
                '-s',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Signals to check(space separated)',
                array_flip(SignalConstants::SIGNALS)
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws FilesystemException
     * @throws JsonException
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $supportedSignals = [];
        $phpVersion = PHP_VERSION;
        $output->writeln('php version: '.$phpVersion);
        $signals = $input->getOption('signals');
        foreach ($signals as $signalName) {
            $process = new Process(['php', dirname(__DIR__).'/../bin/simpleSignalHandler', $signalName]);
            $process->start();
            $process->setTimeout(1);
            try {
                $process->waitUntil(
                    static function ($type, $data) {
                        return 'out' === $type && 'ready' === trim($data);
                    });
                if ($process->isRunning()) {
                    $process->signal(constant($signalName));
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

        $reportPath = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'var', '']);
        $reportName = "{$phpVersion}_supported_signals.json";
        $fp = fopen($reportPath.$reportName, 'wb');
        fwrite($fp, json_encode([
            'phpVersion' => $phpVersion,
            'supportedSignals' => $supportedSignals,
        ]));
        fclose($fp);
        $output->writeln('report was written in '.$reportPath.$reportName);
    }
}
