<?php

declare(strict_types=1);

namespace Mshavliuk\MshavliukSignalEventsBundle\Tests\Command;

use Exception;
use Mshavliuk\MshavliukSignalEventsBundle\Command\SupportedSignalsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

class SupportedSignalsCommandTest extends TestCase
{
    /** @var Application */
    protected $application;

    protected static $processPackageVersion;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$processPackageVersion = self::getProcessVersion();
    }

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
        if ('SIGSTOP' === $signal && !static::isProcessPackageSupportSigstop(static::$processPackageVersion)) {
            $this->markTestSkipped('Skip test for SIGSTOP because of legacy Process package');
        }

        $input = new ArrayInput(
            [
                'command' => 'supported-signals',
                '-s' => [$signal],
                '--no-output' => true,
            ]
        );
        $exitCode = $this->application->run($input);
        $this->assertSame(0, $exitCode);
    }

    /**
     * @throws Exception
     */
    public function testCommandWillCreateReportFileInSpecifiedPlace(): void
    {
        $signal = 'SIGINT';
        $tempFile = tempnam(sys_get_temp_dir(), 'supported_signals_command_test_'.$signal).'.json';
        $input = new ArrayInput(
            [
                'command' => 'supported-signals',
                '-s' => [$signal],
                '-o' => $tempFile,
            ]
        );
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
        $input = new ArrayInput(
            [
                'command' => 'supported-signals',
                '-s' => ['SIGINT'],
                '-o' => $tempFile,
            ]
        );
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

    /**
     * @return string|null
     */
    protected static function getProcessVersion(): ?string
    {
        $process = new Process(['composer', 'show', 'symfony/process']);
        $process->run();
        while (false === strpos($process->getOutput(), 'versions')) {
            usleep(1000);
        }
        if (1 === preg_match('/versions\s*:[\s*]*v(?<version>[\d.]+)/', $process->getOutput(), $matches)) {
            return $matches['version'];
        }

        return null;
    }

    /**
     * Check is Process package "supports" stopped processes. It relates with symfony issue #31548 which have been
     * fixed in modern versions.
     *
     * @see https://github.com/symfony/symfony/issues/31548
     *
     * @param $version
     *
     * @return bool
     */
    protected static function isProcessPackageSupportSigstop($version): bool
    {
        if (version_compare($version, '4.3.0-RC1', '>=')) {
            return true;
        }

        if (0 === strpos($version, '4.2') && version_compare($version, '4.2.9', '>=')) {
            return true;
        }

        if (0 === strpos($version, '3') && version_compare($version, '3.4.28', '>=')) {
            return true;
        }

        return false;
    }
}
