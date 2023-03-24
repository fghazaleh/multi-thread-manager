<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use PHPUnit\Framework\TestCase;

class ThreadSettingsTest extends TestCase
{
    /**
     * @test
     */
    public function testingThreadSettings(): void
    {
        $threadSize = 10;
        $threadStartDelay = 2;
        $pollInterval = 150;
        $threadSettings = new ThreadSettings($threadSize, $threadStartDelay, $pollInterval);

        $this->assertSame($threadSize, $threadSettings->getThreadSize());
        $this->assertSame($threadStartDelay, $threadSettings->getThreadStartDelay());
        $this->assertSame($pollInterval, $threadSettings->getPollInterval());
    }

    public function testingCreatingThreadSettingsFromDefault(): void
    {
        $threadSettings = ThreadSettings::createFromDefault();
        $this->assertSame(8, $threadSettings->getThreadSize());
        $this->assertSame(1, $threadSettings->getThreadStartDelay());
        $this->assertSame(1, $threadSettings->getPollInterval());
    }

    /**
     * @test
     */
    public function testingThreadSettingsWithLessThanZero(): void
    {
        $threadSize = -1;
        $threadStartDelay = -1;
        $pollInterval = -1;
        $threadSettings = new ThreadSettings($threadSize, $threadStartDelay, $pollInterval);

        $this->assertSame(20, $threadSettings->getThreadSize());
        $this->assertSame(0, $threadSettings->getThreadStartDelay());
        $this->assertSame(0, $threadSettings->getPollInterval());
    }
}
