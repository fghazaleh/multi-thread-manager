<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use PHPUnit\Framework\TestCase;

class ProcessSettingsTest extends TestCase
{
    /**
     * @test
     */
    public function testingProcessSettings()
    {
        $threads = 10;
        $processStartDelay = 2;
        $pollInterval = 150;
        $processSettings = new ProcessSettings($threads, $processStartDelay, $pollInterval);

        $this->assertSame($processSettings->getThreads(), $threads);
        $this->assertSame($processSettings->getProcessStartDelay(), $processStartDelay);
        $this->assertSame($processSettings->getPollInterval(), $pollInterval);
    }

    /**
     * @test
     */
    public function testingProcessSettingsWithLessThanZero()
    {
        $threads = -1;
        $processStartDelay = -1;
        $pollInterval = -1;
        $processSettings = new ProcessSettings($threads, $processStartDelay, $pollInterval);

        $this->assertSame($processSettings->getThreads(), 20);
        $this->assertSame($processSettings->getProcessStartDelay(), 0);
        $this->assertSame($processSettings->getPollInterval(), 0);
    }
}