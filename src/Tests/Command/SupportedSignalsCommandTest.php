<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle\Tests\Command;

use Exception;
use Mshavliuk\MshavliukSignalEventsBundle\Command\SupportedSignalsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SupportedSignalsCommandTest extends TestCase
{
    /** @var Application */
    protected $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->add(new SupportedSignalsCommand());
    }

    /**
     * @param string $signal
     *
     * @throws Exception
     * @dataProvider providerFewSignals
     */
    public function testCommandWillExitWithZeroCode($signal): void
    {
        $input = new ArrayInput([
            'command' => 'supported-signals',
            '-s' => [$signal],
        ]);
        $output = new BufferedOutput();
        $exitCode = $this->application->run($input, $output);
        $this->assertSame(0, $exitCode);
    }

    /**
     * @throws Exception
     */
    public function testCommandWillCreateReportFileInSpecifiedPlace(): void
    {
        $signal = 'SIGINT';
        $tempFile = tempnam(sys_get_temp_dir(), 'supported_signals_command_test_'.$signal).'.json';
        $input = new ArrayInput([
            'command' => 'supported-signals',
            '-s' => [$signal],
            '-o' => $tempFile,
        ]);
        $this->application->run($input);
        $this->assertFileExists($tempFile);
        $fileContent = file_get_contents($tempFile);
        $this->assertNotFalse($fileContent);
        $this->assertJson($fileContent);
    }

    /**
     * @throws Exception
     */
    public function testCommandWillReturnErrorCodeIfCannotWriteReportFile(): void
    {
        $tempFile = '/some/unexisted/directory/report.json';
        $this->assertFileNotExists($tempFile);
        $input = new ArrayInput([
            'command' => 'supported-signals',
            '-s' => ['SIGINT'],
            '-o' => $tempFile,
        ]);
        $exitCode = $this->application->run($input);
        $this->assertNotEquals(0, $exitCode);
    }

    public function providerFewSignals(): array
    {
        return [
            'SIGINT' => ['SIGINT'],
            'SIGSTOP ' => ['SIGSTOP'],
            'SIGKILL' => ['SIGKILL'],
        ];
    }
}
