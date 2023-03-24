<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Collection\ThreadCollection;
use FGhazaleh\MultiThreadManager\Contracts\EventInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerEventInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadSettingsInterface;
use FGhazaleh\MultiThreadManager\Events\EventManager;
use FGhazaleh\MultiThreadManager\Exception\InvalidEventArgumentException;
use FGhazaleh\MultiThreadManager\Exception\InvalidThreadException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ThreadManager implements ThreadManagerInterface, ThreadManagerEventInterface
{
    private ThreadCollection $pendingThreads;
    private ThreadCollection $runningThreads;
    private ThreadSettingsInterface $threadSettings;

    private EventManager $events;

    public function __construct(ThreadSettingsInterface $threadSettings)
    {
        $this->threadSettings = $threadSettings;
        $this->pendingThreads = new ThreadCollection();
        $this->runningThreads = new ThreadCollection();
        $this->events = new EventManager();
    }

    /**
     * Create a new instance of thread manager
     *
     * @param int $threadSize
     * @return ThreadManager
     */
    public static function create(int $threadSize): ThreadManager
    {
        return new static(
            new ThreadSettings($threadSize, 0, 120)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addThread($command, array $context = null): void
    {
        $this->pendingThreads->push(
            $this->createThread($command, $context)
        );
        $this->executeNextPendingThread();
        $this->checkAllRunningThreads();
    }

    /**
     * @inheritDoc
     */
    public function listen(string $event, $listener): void
    {
        $this->events->addListener($event, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function wait(): void
    {
        while ($this->hasUnfinishedThreads()) {
            $this->sleep($this->threadSettings->getPollInterval());
            $this->checkAllRunningThreads();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        $this->pendingThreads->clear();
        /**
         * @var ThreadInterface $thread
         * */
        foreach ($this->runningThreads as $thread) {
            $pid = $thread->getPid();
            $thread->stop();
            if ($pid === null) {
                continue;
            }
            $this->runningThreads->remove($pid);
        }

        $this->runningThreads->clear();
    }

    /**
     * Returns whether the manager still has unfinished threads.
     *
     * @return bool
     */
    public function hasUnfinishedThreads(): bool
    {
        return $this->pendingThreads->count() > 0 || $this->runningThreads->count() > 0;
    }

    /**
     * Executes the next pending thread, if the limit of parallel tasks is not yet reached.
     *
     * @return void;
     * @throws InvalidEventArgumentException
     */
    private function executeNextPendingThread(): void
    {
        if ($this->canExecuteNextPendingThread()) {
            $this->sleep($this->threadSettings->getThreadStartDelay());

            $thread = $this->pendingThreads->pull();
            $thread->start();

            $this->events->fire(EventInterface::EVENT_STARTED, $thread);
            $pid = $thread->getPid();

            if ($pid === null) {
                // The task finished before we were able to check its process id.
                $this->checkRunningThread($pid, $thread);
            } else {
                $this->runningThreads->push($thread);
            }
        }
    }

    /**
     * Creates a thread instance based on the command param.
     *
     * @param $command
     * @param array|null $context
     * @return ThreadInterface
     * @throws InvalidThreadException
     */
    private function createThread($command, array $context = null): ThreadInterface
    {
        if ($command instanceof ThreadInterface) {
            return $command;
        } elseif ($command instanceof Process) {
            return new Thread($command, $context);
        } elseif (is_string($command)) {
            return Thread::createFromCommand($command, $context);
        }
        throw new InvalidThreadException('Invalid thread type.');
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
     * Checks the thread whether it has finished.
     *
     * @param int|null $pid
     * @param ThreadInterface $thread
     * @throws Exception\InvalidEventArgumentException
     */
    private function checkRunningThread(?int $pid, ThreadInterface $thread): void
    {
        $this->checkTaskTimeout($thread);
        if (!$thread->getSymfonyProcess()->isRunning()) {
            $this->events->fire(EventInterface::EVENT_FINISHED, $thread);
            if ($pid !== null) {
                $this->runningThreads->remove($pid);
            }
            $this->executeNextPendingThread();
        }
    }

    /**
     * Checks whether the task already timed out.
     *
     * @param ThreadInterface $thread
     * @throws InvalidEventArgumentException
     */
    private function checkTaskTimeout(ThreadInterface $thread): void
    {
        try {
            $thread->getSymfonyProcess()->checkTimeout();
        } catch (ProcessTimedOutException $exception) {
            $this->events->fire(EventInterface::EVENT_TIMEOUT, $thread);
        }
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
