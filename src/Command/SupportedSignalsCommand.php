<?php

namespace Mshavliuk\SignalEventsBundle\Command;

use Mshavliuk\SignalEventsBundle\Service\SignalConstants;
use RuntimeException;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\StringsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\fclose;
use function Safe\json_encode;
use function Safe\sprintf;

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
            )
            ->addOption(
                'output',
                '-o',
                InputOption::VALUE_OPTIONAL,
                'Output report path'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws FilesystemException
     * @throws JsonException
     * @throws StringsException
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phpVersion = PHP_VERSION;
        $output->writeln('php version: '.$phpVersion);
        $signals = $input->getOption('signals');
        $supportedSignals = [];
        foreach ($signals as $signalName) {
            [
                'message' => $message,
                'support' => $support,
            ] = $this->checkSignalSupport($signalName);
            if ($support) {
                $supportedSignals[] = $signalName;
            }
            $output->writeln($message);
        }

        if ($input->hasOption('output')) {
            $reportFilePath = $input->getOption('output');
        } else {
            $reportFilePath = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'var', '']).$phpVersion.'_supported_signals.json';
        }

        $fp = fopen($reportFilePath, 'wb');
        fwrite($fp, json_encode([
            'phpVersion' => $phpVersion,
            'supportedSignals' => $supportedSignals,
        ]));
        fclose($fp);
        $output->writeln('report was written in '.$reportFilePath);
    }

    /**
     * @param $signalName
     *
     * @throws StringsException
     *
     * @return array
     */
    protected function checkSignalSupport($signalName): array
    {
        $executedCode = str_replace('%signal%', constant($signalName), self::PHP_CODE);
        $process = new Process(['php', '-r', $executedCode]);
        $process->start();
        $process->setTimeout(1);
        try {
            $process->waitUntil(
                static function ($type, $data) {
                    return 'out' === $type && 'ready' === trim($data);
                });
            $process->signal(constant($signalName));
            $process->wait();
        } catch (RuntimeException $e) {
            return [
                'message' => sprintf('%s: fail (%s)', $signalName, $e->getMessage()),
                'support' => false,
            ];
        }
        if (!$process->isSuccessful()) {
            return [
                'message' => sprintf('%s: fail (%s)', $signalName, 'process was halted'),
                'support' => false,
            ];
        }

        return [
            'message' => sprintf('%s: success (%s)', $signalName, $process->getExitCodeText()),
            'support' => true,
        ];
    }

    protected const PHP_CODE = <<<'PHP'
        pcntl_async_signals(true);
        if(pcntl_signal(%signal%, function () { echo 'success'.PHP_EOL; die(0); })) {
            echo 'ready'.PHP_EOL;
        } else {
            echo 'unknown signal'.PHP_EOL;
            die(1);
        }
        usleep(1e7);
PHP;
}
