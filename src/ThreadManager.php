<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Collection\TaskCollection;
use FGhazaleh\MultiThreadManager\Contracts\EventInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerEventInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadSettingsInterface;
use FGhazaleh\MultiThreadManager\Contracts\TaskInterface;
use FGhazaleh\MultiThreadManager\Events\EventManager;
use FGhazaleh\MultiThreadManager\Exception\InvalidEventArgumentException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ThreadManager implements ThreadManagerInterface, ThreadManagerEventInterface
{

    /**
     * @var TaskCollection;
     */
    private $pendingTasks;

    /**
     * @var TaskCollection;
     */
    private $runningTasks;
    /**
     * @var ThreadSettingsInterface
     */
    private $processSettings;

    private $events;

    public function __construct(ThreadSettingsInterface $processSettings)
    {
        $this->processSettings = $processSettings;
        $this->pendingTasks = new TaskCollection();
        $this->runningTasks = new TaskCollection();
        $this->events = new EventManager();
    }

    /**
     * Create a new instance of process manager
     *
     * @param int $thread
     * @return ThreadManager
     */
    public static function create(int $thread): ThreadManager
    {
        return new static(
            new ThreadSettings($thread, 0, 120)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function add($command, array $context = null): void
    {
        $this->pendingTasks->push(
            $this->createTask($command, $context)
        );
        $this->executeNextPendingTask();
        $this->checkAllRunningTasks();
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
        // TODO: Implement wait() method.
    }

    public function terminate(): void
    {
        $this->pendingTasks->clear();
        /**
         * @var TaskInterface $task
         * */
        foreach ($this->runningTasks as $task) {
            $task->stop();
            $this->runningTasks->remove($task->getPid());
        }
    }

    /**
     * Executes the next pending task, if the limit of parallel tasks is not yet reached.
     * @throws InvalidEventArgumentException
     * @return void;
     */
    private function executeNextPendingTask(): void
    {
        if ($this->canExecuteNextPendingTask()) {
            $this->sleep($this->processSettings->getThreadStartDelay());

            $task = $this->pendingTasks->pull();
            $task->start();

            $this->events->fire(EventInterface::EVENT_STARTED, $task);
            $pid = $task->getPid();

            if ($pid === null) {
                $this->runningTasks->push($task);
            } else {
                // The task finished before we were able to check its process id.
                $this->checkRunningTask($pid, $task);
            }
        }
    }

    /**
     * Creates a task instance based on the command param.
     *
     * @param $command
     * @param array|null $context
     * @return TaskInterface
     */
    private function createTask($command, array $context = null): TaskInterface
    {
        if ($command instanceof TaskInterface) {
            return $command;
        } elseif ($command instanceof Process) {
            return new Task($command, $context);
        } elseif (\is_string($command)) {
            return new Task(
                Process::fromShellCommandline($command),
                $context
            );
        }
        throw new \InvalidArgumentException('Invalid command');
    }

    /**
     * Checks whether a pending task is available and can be executed.
     *
     * @return bool
     */
    private function canExecuteNextPendingTask(): bool
    {
        return $this->pendingTasks->count() > 0 &&
            $this->runningTasks->count() < $this->processSettings->getThreads();
    }

    /**
     * Checks the running tasks whether they have finished.
     */
    private function checkAllRunningTasks(): void
    {
        foreach ($this->runningTasks as $pid => $task) {
            $this->checkRunningTask($pid, $task);
        }
    }

    /**
     * Checks the task whether it has finished.
     *
     * @param int|null $pid
     * @param TaskInterface $task
     * @throws Exception\InvalidEventArgumentException
     */
    private function checkRunningTask(?int $pid, TaskInterface $task): void
    {
        $this->checkTaskTimeout($task);
        if (!$task->getCommand()->isRunning()) {
            $this->events->fire(EventInterface::EVENT_FINISHED, $task);
            if ($pid !== null) {
                $this->runningTasks->remove($pid);
            }
            $this->executeNextPendingTask();
        }
    }

    /**
     * Checks whether the task already timed out.
     *
     * @param TaskInterface $task
     * @throws InvalidEventArgumentException
     */
    private function checkTaskTimeout(TaskInterface $task): void
    {
        try {
            $task->getCommand()->checkTimeout();
        } catch (ProcessTimedOutException $exception) {
            $this->events->fire(EventInterface::EVENT_TIMEOUT, $task);
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
