<?php

namespace Mshavliuk\SignalEventsBundle\Tests\Command;

use Exception;
use Mshavliuk\SignalEventsBundle\Command\SupportedSignalsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SupportedSignalsCommandTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCommandWillCreateReportFileAfterExecution()
    {
        $application = new Application();
        $application->setAutoExit(false);
        $application->add(new SupportedSignalsCommand());
        $input = new ArrayInput([
            'command' => 'supported-signals',
            '-s' => ['SIGINT'],
        ]);
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);
        $this->assertSame(0, $exitCode);
        $outputLines = explode(PHP_EOL, trim($output->fetch()));
        $filePathLine = array_pop($outputLines);
        preg_match('/report was written in (?<file_path>[\w.\\/]+)/', $filePathLine, $matches);
        $this->assertFileExists($matches['file_path']);
    }
}
