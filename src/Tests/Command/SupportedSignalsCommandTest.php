<?php

namespace Mshavliuk\SignalEventsBundle\Tests\Command;

use Exception;
use function file_get_contents;
use Mshavliuk\SignalEventsBundle\Command\SupportedSignalsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SupportedSignalsCommandTest extends TestCase
{
    /**
     * @param $signal
     * @throws Exception
     * @dataProvider providerFewSignals
     */
    public function testCommandWillCreateReportFileAfterExecution($signal): void
    {
        $application = new Application();
        $application->setAutoExit(false);
        $application->add(new SupportedSignalsCommand());
        $tempFile = tempnam(sys_get_temp_dir(), 'supported_signals_command_test_'.$signal).'.json';
        $input = new ArrayInput([
            'command' => 'supported-signals',
            '-s' => [$signal],
            '-o' => $tempFile,
        ]);
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);
        $this->assertSame(0, $exitCode);
        $this->assertFileExists($tempFile);
        $this->assertJson(file_get_contents($tempFile));
    }

    public function providerFewSignals(): array
    {
        return [
            'SIGINT' => ['SIGINT'],
            'SIGSTOP' => ['SIGSTOP'],
            'SIGKILL' => ['SIGKILL'],
        ];
    }
}
