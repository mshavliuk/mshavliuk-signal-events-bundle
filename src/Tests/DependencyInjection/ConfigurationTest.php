<?php

namespace Mshavliuk\MshavliukSignalEventsBundle\Tests\DependencyInjection;

use Mshavliuk\MshavliukSignalEventsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testConfigurationLoadDefaults(): void
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), []);
        $this->assertIsArray($config['startup_events']);
        $this->assertNotEmpty($config['startup_events']);

        $this->assertIsArray($config['handle_signals']);
        $this->assertNotEmpty($config['handle_signals']);
    }
}
