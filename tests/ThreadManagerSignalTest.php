<?php

/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ThreadManagerSignalTest extends TestCase
{
    protected function setUp(): void
    {
        if (!function_exists('pcntl_signal') || !function_exists('posix_kill')) {
            $this->markTestSkipped('The pcntl and posix extensions are required.');
        }
    }

    /**
     * @return array<string, array{int}>
     */
    public static function signals(): array
    {
        return ['SIGTERM' => [SIGTERM], 'SIGINT' => [SIGINT]];
    }

    #[Test]
    #[DataProvider('signals')]
    public function itShouldTerminateAllThreadsAndReturnWhenSignaled(int $signal): void
    {
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/Fixtures/signal_child.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );
        $this->assertIsResource($process);

        // Wait until both threads have started.
        $childPids = [];
        $deadline = microtime(true) + 10;
        while (count($childPids) < 2 && microtime(true) < $deadline) {
            $line = fgets($pipes[1]);
            if ($line !== false && preg_match('/^pid=(\d+)/', $line, $m)) {
                $childPids[] = (int) $m[1];
            }
        }
        $this->assertCount(2, $childPids, 'Both threads should have started.');

        $managerPid = proc_get_status($process)['pid'];
        posix_kill($managerPid, $signal);

        stream_set_timeout($pipes[1], 10);
        $output = stream_get_contents($pipes[1]);
        $exitCode = proc_close($process);

        $this->assertStringContainsString('done interrupted=1 results=0', $output);
        $this->assertSame(0, $exitCode);
        foreach ($childPids as $pid) {
            // Give the OS a moment to reap the terminated child.
            $gone = false;
            for ($i = 0; $i < 20 && !$gone; $i++) {
                $gone = !posix_kill($pid, 0);
                $gone || usleep(100000);
            }
            $this->assertTrue($gone, "Child process {$pid} must be stopped.");
        }
    }

    #[Test]
    public function itShouldNotBeInterruptedByDefault(): void
    {
        $this->assertFalse(ThreadManager::create(1)->isInterrupted());
    }
}
