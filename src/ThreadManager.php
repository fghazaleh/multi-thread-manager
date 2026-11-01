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
use Override;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ThreadManager implements ThreadManagerInterface, ThreadManagerEventInterface
{
    private readonly ThreadCollection $pendingThreads;
    private readonly ThreadCollection $runningThreads;
    private readonly EventManager $events;

    public function __construct(private readonly ThreadSettingsInterface $threadSettings)
    {
        $this->pendingThreads = new ThreadCollection();
        $this->runningThreads = new ThreadCollection();
        $this->events = new EventManager();
    }

    /**
     * Create a new instance of thread manager.
     */
    public static function create(int $threadSize): self
    {
        return new self(
            new ThreadSettings(threadSize: $threadSize, threadStartDelay: 0, pollInterval: 120)
        );
    }

    #[Override]
    public function addThread(string|Process|ThreadInterface $command, ?array $context = null): void
    {
        $this->pendingThreads->push(
            $this->createThread($command, $context)
        );
        $this->executeNextPendingThread();
        $this->checkAllRunningThreads();
    }

    #[Override]
    public function listen(Event $event, callable|ListenerInterface $listener): void
    {
        $this->events->addListener($event, $listener);
    }

    #[Override]
    public function wait(): void
    {
        while ($this->hasUnfinishedThreads()) {
            $this->sleep($this->threadSettings->getPollInterval());
            $this->checkAllRunningThreads();
        }
    }

    #[Override]
    public function terminate(): void
    {
        $this->pendingThreads->clear();
        foreach ($this->runningThreads as $thread) {
            $thread->stop();
        }
        $this->runningThreads->clear();
    }

    /**
     * Returns whether the manager still has unfinished threads.
     */
    public function hasUnfinishedThreads(): bool
    {
        return $this->pendingThreads->count() > 0 || $this->runningThreads->count() > 0;
    }

    /**
     * Executes the next pending thread, if the limit of parallel tasks is not yet reached.
     */
    private function executeNextPendingThread(): void
    {
        if ($this->canExecuteNextPendingThread()) {
            $this->sleep($this->threadSettings->getThreadStartDelay());

            $thread = $this->pendingThreads->pull();
            $thread->start();

            $this->events->fire(Event::Started, $thread);
            $pid = $thread->getPid();

            if ($pid === null) {
                // The task finished before we were able to check its process id.
                $this->checkRunningThread($pid, $thread);
            } else {
                $this->runningThreads->push($thread, $pid);
            }
        }
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
        return $this->pendingThreads->count() > 0 &&
            $this->runningThreads->count() < $this->threadSettings->getThreadSize();
    }

    /**
     * Checks the running threads whether they have finished.
     */
    private function checkAllRunningThreads(): void
    {
        foreach ($this->runningThreads as $pid => $thread) {
            $this->checkRunningThread($pid, $thread);
        }
    }

    /**
     * Checks the thread whether it has finished or timed out.
     *
     * A timed out thread is stopped and removed without firing the finished event.
     */
    private function checkRunningThread(?int $pid, ThreadInterface $thread): void
    {
        if ($this->hasTimedOut($thread)) {
            $thread->stop();
            $this->events->fire(Event::Timeout, $thread);
        } elseif ($thread->getSymfonyProcess()->isRunning()) {
            return;
        } else {
            $this->events->fire(Event::Finished, $thread);
        }

        if ($pid !== null) {
            $this->runningThreads->remove($pid);
        }
        $this->executeNextPendingThread();
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
