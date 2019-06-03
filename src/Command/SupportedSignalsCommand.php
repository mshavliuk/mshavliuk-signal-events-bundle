<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle\Command;

use Exception;
use Mshavliuk\MshavliukSignalEventsBundle\Service\SignalConstants;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SupportedSignalsCommand extends Command
{
    public function __construct()
    {
        parent::__construct('supported-signals');
    }

    protected function configure(): void
    {
        /** @var array<string> $defaultSignals */
        $defaultSignals = array_values(array_flip(SignalConstants::SIGNALS));

        $this->setDescription('Check which signals can be handled by php pcntl_signal function')
            ->addOption(
                'signals',
                '-s',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Signals to check(space separated)',
                $defaultSignals
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
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phpVersion = PHP_VERSION;
        $output->writeln('php version: '.$phpVersion);
        /** @var array<string> $signals */
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

        if (null !== $input->getOption('output')) {
            $reportFilePath = (string) $input->getOption('output');
        } else {
            $fileName = $phpVersion.'_supported_signals.json';
            $reportFilePath = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'var', '']).$fileName;
        }

        if ($this->writeReportFile($reportFilePath, $supportedSignals, $phpVersion)) {
            $output->writeln('report was written in '.$reportFilePath);

            return 0;
        }

        $output->writeln('there is some errors during write report file');

        return 1;
    }

    /**
     * @param string $reportFilePath
     * @param array<string> $supportedSignals
     * @param string $phpVersion
     *
     * @return bool
     */
    protected function writeReportFile(string $reportFilePath, array $supportedSignals, string $phpVersion): bool
    {
        try {
            $fp = fopen($reportFilePath, 'wb');
            if (false !== $fp) {
                $content = json_encode(
                    [
                        'phpVersion' => $phpVersion,
                        'supportedSignals' => $supportedSignals,
                    ]
                );
                if (false !== $content) {
                    fwrite($fp, $content);
                    fclose($fp);

                    return true;
                }
                fclose($fp);
            }
        } catch (Exception $exception) {
        }

        return false;
    }

    /**
     * @param string $signalName
     *
     * @return array
     */
    protected function checkSignalSupport(string $signalName): array
    {
        $executedCode = str_replace('%signal%', constant($signalName), self::PHP_CODE);
        $process = new Process(['php', '-r', $executedCode]);
        $process->start();
        $process->setTimeout(1);
        try {
            $process->waitUntil(
                static function ($type, $data) {
                    return 'out' === $type && 'ready' === trim($data);
                }
            );
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
        while(true) {
            usleep(1e7);
        }
PHP;
}
