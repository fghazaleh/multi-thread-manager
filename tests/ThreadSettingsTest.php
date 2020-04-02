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
    public function testingThreadSettings()
    {
        $threadSize = 10;
        $threadStartDelay = 2;
        $pollInterval = 150;
        $threadSettings = new ThreadSettings($threadSize, $threadStartDelay, $pollInterval);

        $this->assertSame($threadSettings->getThreadSize(), $threadSize);
        $this->assertSame($threadSettings->getThreadStartDelay(), $threadStartDelay);
        $this->assertSame($threadSettings->getPollInterval(), $pollInterval);
    }

    /**
     * @test
     */
    public function testingThreadSettingsWithLessThanZero()
    {
        $threadSize = -1;
        $threadStartDelay = -1;
        $pollInterval = -1;
        $threadSettings = new ThreadSettings($threadSize, $threadStartDelay, $pollInterval);

        $this->assertSame($threadSettings->getThreadSize(), 20);
        $this->assertSame($threadSettings->getThreadStartDelay(), 0);
        $this->assertSame($threadSettings->getPollInterval(), 0);
    }
}
