<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use FGhazaleh\MultiProcessManager\Collection\TaskCollection;
use FGhazaleh\MultiProcessManager\Contracts\EventInterface;
use FGhazaleh\MultiProcessManager\Contracts\ProcessManagerEventInterface;
use FGhazaleh\MultiProcessManager\Contracts\ProcessManagerInterface;
use FGhazaleh\MultiProcessManager\Contracts\ProcessSettingsInterface;
use FGhazaleh\MultiProcessManager\Contracts\TaskInterface;
use FGhazaleh\MultiProcessManager\Events\EventContainer;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ProcessManager implements ProcessManagerInterface, ProcessManagerEventInterface
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
     * @var ProcessSettingsInterface
     */
    private $processSettings;

    private $events;

    public function __construct(ProcessSettingsInterface $processSettings)
    {
        $this->processSettings = $processSettings;
        $this->pendingTasks = new TaskCollection();
        $this->runningTasks = new TaskCollection();
        $this->events = new EventContainer();
    }

    /**
     * Create a new instance of process manager
     *
     * @param int $thread
     * @return ProcessManager
     */
    public static function create(int $thread): ProcessManager
    {
        return new static(
            new ProcessSettings($thread, 0, 120)
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
    public function listenOn(string $event, $listener): void
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
     * @return void;
     */
    private function executeNextPendingTask(): void
    {
        if ($this->canExecuteNextPendingTask()) {
            $this->sleep($this->processSettings->getProcessStartDelay());

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
     */
    private function checkTaskTimeout(TaskInterface $task): void
    {
        try {
            $task->getCommand()->checkTimeout();
        } catch (ProcessTimedOutException $exception) {
            //handle time out
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
