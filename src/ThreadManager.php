<?php

/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Collection\ThreadCollection;
use FGhazaleh\MultiThreadManager\Contracts\ListenerInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerEventInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadSettingsInterface;
use FGhazaleh\MultiThreadManager\Events\EventManager;
use LogicException;
use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ThreadManager implements ThreadManagerInterface, ThreadManagerEventInterface
{
    private readonly ThreadCollection $pendingThreads;
    private readonly ThreadCollection $runningThreads;
    private readonly EventManager $events;

    /** @var list<ThreadResult> */
    private array $results = [];

    /** @var array<int, float> start time (hrtime, seconds) by spl_object_id of the thread */
    private array $startedAt = [];

    /** @var array<int, RetryPolicy> retry policy by spl_object_id of the thread */
    private array $retryPolicies = [];

    /** @var array<int, int> number of retries already scheduled by spl_object_id of the thread */
    private array $retries = [];

    /** @var array<int, array{thread: ThreadInterface, readyAt: float}> threads waiting for their next attempt */
    private array $waitingForRetry = [];

    /** @var list<callable(ThreadInterface, OutputType, string): mixed> */
    private array $outputListeners = [];

    private bool $interrupted = false;

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly ThreadSettingsInterface $threadSettings,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->pendingThreads = new ThreadCollection();
        $this->runningThreads = new ThreadCollection();
        $this->events = new EventManager();
    }

    /**
     * Create a new instance of thread manager.
     */
    public static function create(int $threadSize, bool $failFast = false, ?LoggerInterface $logger = null): self
    {
        return new self(
            new ThreadSettings(threadSize: $threadSize, threadStartDelay: 0, pollInterval: 120, failFast: $failFast),
            $logger,
        );
    }

    /**
     * Registers a callback that receives the output of running threads while they run,
     * in chunks, instead of only at the end. The last chunk is delivered before the
     * thread's Finished / Failed / Timeout event fires.
     *
     * @param callable(ThreadInterface, OutputType, string): mixed $callback
     */
    public function onOutput(callable $callback): void
    {
        $this->outputListeners[] = $callback;
    }

    /**
     * Terminates all threads when the process receives SIGINT or SIGTERM, so that
     * wait() returns instead of leaving orphaned child processes behind.
     *
     * Replaces any existing SIGINT / SIGTERM handler. Requires the pcntl extension
     * (not available on Windows).
     *
     * @throws LogicException when the pcntl extension is not available
     */
    public function handleSignals(): self
    {
        if (!function_exists('pcntl_signal') || !function_exists('pcntl_async_signals')) {
            throw new LogicException('Signal handling requires the pcntl extension.');
        }

        pcntl_async_signals(true);
        $handler = function (int $signal): void {
            $this->interrupted = true;
            $this->logger->warning('Signal received, terminating all threads.', ['signal' => $signal]);
            $this->terminate();
        };
        pcntl_signal(SIGINT, $handler);
        pcntl_signal(SIGTERM, $handler);

        return $this;
    }

    /**
     * Whether the manager was stopped by a signal (see handleSignals()).
     */
    public function isInterrupted(): bool
    {
        return $this->interrupted;
    }

    #[Override]
    public function addThread(string|Process|ThreadInterface $command, ?array $context = null, ?RetryPolicy $retry = null): void
    {
        $thread = $this->createThread($command, $context);
        if ($retry !== null) {
            $this->retryPolicies[spl_object_id($thread)] = $retry;
        }
        $this->pendingThreads->push($thread);
        $this->executeNextPendingThread();
        $this->checkAllRunningThreads();
    }

    #[Override]
    public function listen(Event $event, callable|ListenerInterface $listener): void
    {
        $this->events->addListener($event, $listener);
    }

    #[Override]
    public function wait(): array
    {
        while ($this->hasUnfinishedThreads()) {
            $this->sleep($this->threadSettings->getPollInterval());
            $this->checkAllRunningThreads();
        }

        return $this->results;
    }

    #[Override]
    public function getResults(): array
    {
        return $this->results;
    }

    #[Override]
    public function terminate(): void
    {
        $this->pendingThreads->clear();
        foreach ($this->runningThreads as $thread) {
            $thread->stop();
        }
        $this->runningThreads->clear();
        $this->startedAt = [];
        $this->waitingForRetry = [];
    }

    /**
     * Returns whether the manager still has unfinished threads.
     */
    public function hasUnfinishedThreads(): bool
    {
        return $this->pendingThreads->count() > 0
            || $this->runningThreads->count() > 0
            || $this->waitingForRetry !== [];
    }

    /**
     * Executes the next pending thread, if the limit of parallel tasks is not yet reached.
     */
    private function executeNextPendingThread(): void
    {
        if ($this->canExecuteNextPendingThread()) {
            $this->sleep($this->threadSettings->getThreadStartDelay());

            $thread = $this->pendingThreads->pull();
            $this->startedAt[spl_object_id($thread)] = hrtime(true) / 1e9;
            $thread->start();

            $this->logger->info('Thread started.', $this->logContext($thread));
            $this->events->fire(Event::Started, $thread);
            $this->track($thread);
        }
    }

    /**
     * Tracks a started thread by its pid, or checks it right away when it already finished.
     */
    private function track(ThreadInterface $thread): void
    {
        $pid = $thread->getPid();

        if ($pid === null) {
            // The task finished before we were able to check its process id.
            $this->checkRunningThread($pid, $thread);
        } else {
            $this->runningThreads->push($thread, $pid);
        }
    }

    /**
     * Restarts the threads whose retry delay elapsed, as far as free slots are available.
     */
    private function startDueRetries(): void
    {
        foreach ($this->waitingForRetry as $id => $waiting) {
            if (!$this->hasFreeSlot()) {
                return;
            }
            if ($waiting['readyAt'] > hrtime(true) / 1e9) {
                continue;
            }

            unset($this->waitingForRetry[$id]);
            $thread = $waiting['thread'];
            $this->startedAt[$id] = hrtime(true) / 1e9;
            $thread->restart();
            $this->logger->info('Thread restarted.', $this->logContext($thread) + ['retry' => $this->retries[$id] ?? 0]);
            $this->track($thread);
        }
    }

    /**
     * Schedules the next attempt of a failed thread. Returns false when no retries are left.
     */
    private function scheduleRetry(ThreadInterface $thread): bool
    {
        $id = spl_object_id($thread);
        $policy = $this->retryPolicies[$id] ?? null;
        $retries = $this->retries[$id] ?? 0;

        if ($policy === null || $retries >= $policy->maxRetries) {
            return false;
        }

        $this->retries[$id] = ++$retries;
        $this->waitingForRetry[$id] = [
            'thread' => $thread,
            'readyAt' => hrtime(true) / 1e9 + $policy->delayFor($retries) / 1000,
        ];
        unset($this->startedAt[$id]);
        $this->logger->notice('Thread failed, retrying.', $this->logContext($thread) + [
            'retry' => $retries,
            'max_retries' => $policy->maxRetries,
            'delay_ms' => $policy->delayFor($retries),
        ]);
        $this->events->fire(Event::Retrying, $thread);

        return true;
    }

    /**
     * Creates a thread instance based on the command param.
     *
     * @param array<string, mixed>|null $context
     */
    private function createThread(string|Process|ThreadInterface $command, ?array $context): ThreadInterface
    {
        return match (true) {
            $command instanceof ThreadInterface => $command,
            $command instanceof Process => new Thread($command, $context),
            default => Thread::createFromCommand($command, $context),
        };
    }

    /**
     * Checks whether a pending thread is available and can be executed.
     *
     * @return bool
     */
    private function canExecuteNextPendingThread(): bool
    {
        return $this->pendingThreads->count() > 0 && $this->hasFreeSlot();
    }

    private function hasFreeSlot(): bool
    {
        return $this->runningThreads->count() < $this->threadSettings->getThreadSize();
    }

    /**
     * Checks the running threads whether they have finished.
     */
    private function checkAllRunningThreads(): void
    {
        foreach ($this->runningThreads as $pid => $thread) {
            $this->checkRunningThread($pid, $thread);
        }
        $this->startDueRetries();
    }

    /**
     * Checks the thread whether it has finished, failed or timed out.
     *
     * A timed out thread is stopped and removed. A failed or timed out thread with retries left fires
     * Retrying and waits for its next attempt; otherwise exactly one of Finished, Failed or Timeout is fired.
     */
    private function checkRunningThread(?int $pid, ThreadInterface $thread): void
    {
        if ($this->interrupted) {
            return;
        }

        if ($this->hasTimedOut($thread)) {
            $thread->stop();
            $status = ThreadStatus::TimedOut;
        } elseif ($thread->getSymfonyProcess()->isRunning()) {
            $this->flushOutput($thread);

            return;
        } else {
            $status = $thread->getSymfonyProcess()->getExitCode() === 0
                ? ThreadStatus::Succeeded
                : ThreadStatus::Failed;
        }

        $this->flushOutput($thread);

        if ($pid !== null) {
            $this->runningThreads->remove($pid);
        }

        if ($status !== ThreadStatus::Succeeded && $this->scheduleRetry($thread)) {
            $this->startDueRetries();
            $this->executeNextPendingThread();

            return;
        }

        $result = $this->createResult($thread, $status);
        $this->results[] = $result;
        $this->logResult($result);
        $this->events->fire(
            match ($status) {
                ThreadStatus::Succeeded => Event::Finished,
                ThreadStatus::Failed => Event::Failed,
                ThreadStatus::TimedOut => Event::Timeout,
            },
            $thread,
        );

        if ($status !== ThreadStatus::Succeeded && $this->threadSettings->isFailFast()) {
            $this->terminate();

            return;
        }

        $this->executeNextPendingThread();
    }

    private function createResult(ThreadInterface $thread, ThreadStatus $status): ThreadResult
    {
        $process = $thread->getSymfonyProcess();
        $id = spl_object_id($thread);
        $startedAt = $this->startedAt[$id] ?? null;
        unset($this->startedAt[$id]);

        return new ThreadResult(
            thread: $thread,
            status: $status,
            exitCode: $process->getExitCode(),
            output: $process->getOutput(),
            errorOutput: $process->getErrorOutput(),
            duration: $startedAt === null ? 0.0 : hrtime(true) / 1e9 - $startedAt,
        );
    }

    /**
     * Delivers the output produced since the last call to the output listeners.
     */
    private function flushOutput(ThreadInterface $thread): void
    {
        if ($this->outputListeners === []) {
            return;
        }

        $process = $thread->getSymfonyProcess();
        foreach ([
            [OutputType::Stdout, $process->getIncrementalOutput()],
            [OutputType::Stderr, $process->getIncrementalErrorOutput()],
        ] as [$type, $chunk]) {
            if ($chunk === '') {
                continue;
            }
            foreach ($this->outputListeners as $listener) {
                $listener($thread, $type, $chunk);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function logContext(ThreadInterface $thread): array
    {
        return [
            'pid' => $thread->getPid(),
            'command' => $thread->getSymfonyProcess()->getCommandLine(),
            'context' => $thread->getContext(),
        ];
    }

    private function logResult(ThreadResult $result): void
    {
        $context = $this->logContext($result->thread) + [
            'exit_code' => $result->exitCode,
            'duration' => round($result->duration, 3),
        ];

        match ($result->status) {
            ThreadStatus::Succeeded => $this->logger->info('Thread finished.', $context),
            ThreadStatus::Failed => $this->logger->error('Thread failed.', $context),
            ThreadStatus::TimedOut => $this->logger->warning('Thread timed out.', $context),
        };
    }

    private function hasTimedOut(ThreadInterface $thread): bool
    {
        try {
            $thread->getSymfonyProcess()->checkTimeout();
        } catch (ProcessTimedOutException) {
            return true;
        }

        return false;
    }

    /**
     * Sleeps for the specified number of milliseconds.
     *
     * @param int $milliseconds
     * @return void
     */
    private function sleep(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }
}
