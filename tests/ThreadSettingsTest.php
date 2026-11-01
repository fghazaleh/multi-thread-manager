<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Exception\InvalidSettingsException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ThreadSettingsTest extends TestCase
{
    #[Test]
    public function testingThreadSettings(): void
    {
        $threadSize = 10;
        $threadStartDelay = 2;
        $pollInterval = 150;
        $threadSettings = new ThreadSettings($threadSize, $threadStartDelay, $pollInterval);

        $this->assertSame($threadSize, $threadSettings->getThreadSize());
        $this->assertSame($threadStartDelay, $threadSettings->getThreadStartDelay());
        $this->assertSame($pollInterval, $threadSettings->getPollInterval());
        $this->assertFalse($threadSettings->isFailFast());
        $this->assertTrue((new ThreadSettings(1, 0, 0, failFast: true))->isFailFast());
    }

    #[Test]
    public function testingCreatingThreadSettingsFromDefault(): void
    {
        $threadSettings = ThreadSettings::createFromDefault();
        $this->assertSame(8, $threadSettings->getThreadSize());
        $this->assertSame(1, $threadSettings->getThreadStartDelay());
        $this->assertSame(1, $threadSettings->getPollInterval());
    }

    #[Test]
    public function itShouldNotCapTheThreadSize(): void
    {
        $this->assertSame(50, (new ThreadSettings(50, 0, 0))->getThreadSize());
    }

    #[Test]
    #[DataProvider('invalidSettings')]
    public function itShouldRejectInvalidSettings(int $size, int $delay, int $poll, string $message): void
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage($message);

        new ThreadSettings($size, $delay, $poll);
    }

    /**
     * @return array<string, array{int, int, int, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'zero thread size' => [0, 0, 0, 'Thread size must be at least 1.'],
            'negative thread size' => [-1, 0, 0, 'Thread size must be at least 1.'],
            'negative start delay' => [1, -1, 0, 'Thread start delay must not be negative.'],
            'negative poll interval' => [1, 0, -1, 'Poll interval must not be negative.'],
        ];
    }
}
