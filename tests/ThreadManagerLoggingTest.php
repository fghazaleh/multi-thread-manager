<?php

/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\Process\Process;

class ThreadManagerLoggingTest extends TestCase
{
    #[Test]
    public function itShouldLogStartAndSuccessfulFinish(): void
    {
        $logger = new RecordingLogger();
        $threadManager = ThreadManager::create(1, logger: $logger);

        $threadManager->addThread('php -r "exit(0);"', ['job' => 7]);
        $threadManager->wait();

        $this->assertSame(['info', 'info'], $logger->levels());
        $this->assertSame(['Thread started.', 'Thread finished.'], $logger->messages());
        $finished = $logger->records[1]['context'];
        $this->assertSame(0, $finished['exit_code']);
        $this->assertSame(['job' => 7], $finished['context']);
        $this->assertIsFloat($finished['duration']);
        $this->assertStringContainsString('exit(0)', $finished['command']);
    }

    #[Test]
    public function itShouldLogFailuresAsErrors(): void
    {
        $logger = new RecordingLogger();
        $threadManager = ThreadManager::create(1, logger: $logger);

        $threadManager->addThread('php -r "exit(4);"');
        $threadManager->wait();

        $this->assertSame(['info', 'error'], $logger->levels());
        $this->assertSame('Thread failed.', $logger->records[1]['message']);
        $this->assertSame(4, $logger->records[1]['context']['exit_code']);
    }

    #[Test]
    public function itShouldLogTimeoutsAsWarnings(): void
    {
        $logger = new RecordingLogger();
        $threadManager = ThreadManager::create(1, logger: $logger);
        $process = Process::fromShellCommandline('exec php -r "sleep(500);"');
        $process->setTimeout(0.3);

        $threadManager->addThread($process);
        $threadManager->wait();

        $this->assertSame(['info', 'warning'], $logger->levels());
        $this->assertSame('Thread timed out.', $logger->records[1]['message']);
    }

    #[Test]
    public function itShouldLogRetries(): void
    {
        $logger = new RecordingLogger();
        $threadManager = ThreadManager::create(1, logger: $logger);

        $threadManager->addThread('php -r "exit(1);"', null, new RetryPolicy(maxRetries: 1, delay: 10));
        $threadManager->wait();

        $this->assertSame(['Thread started.', 'Thread failed, retrying.', 'Thread restarted.', 'Thread failed.'], $logger->messages());
        $this->assertSame(['info', 'notice', 'info', 'error'], $logger->levels());
        $retry = $logger->records[1]['context'];
        $this->assertSame(1, $retry['retry']);
        $this->assertSame(1, $retry['max_retries']);
        $this->assertSame(10, $retry['delay_ms']);
    }

    #[Test]
    public function itShouldWorkWithoutALogger(): void
    {
        $threadManager = ThreadManager::create(1);

        $threadManager->addThread('php -r "exit(0);"');

        $this->assertCount(1, $threadManager->wait());
    }
}

final class RecordingLogger extends AbstractLogger
{
    /** @var list<array{level: string, message: string, context: array<string, mixed>}> */
    public array $records = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->records[] = ['level' => (string) $level, 'message' => (string) $message, 'context' => $context];
    }

    /**
     * @return list<string>
     */
    public function levels(): array
    {
        return array_column($this->records, 'level');
    }

    /**
     * @return list<string>
     */
    public function messages(): array
    {
        return array_column($this->records, 'message');
    }
}
