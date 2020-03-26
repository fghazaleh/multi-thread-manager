<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use FGhazaleh\MultiProcessManager\Collection\TaskCollection;
use FGhazaleh\MultiProcessManager\Contacts\ProcessManagerInterface;
use FGhazaleh\MultiProcessManager\Contacts\ProcessSettingsInterface;
use FGhazaleh\MultiProcessManager\Contacts\TaskInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ProcessManager implements ProcessManagerInterface
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

    public function __construct(ProcessSettingsInterface $processSettings)
    {
        $this->processSettings = $processSettings;
        $this->pendingTasks = new TaskCollection();
        $this->runningTasks = new TaskCollection();
    }

    /**
     * Create a new instance of process manager
     *
     * @param int $thread
     * @return ProcessManagerInterface
     */
    public static function create(int $thread): ProcessManagerInterface
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

            //@todo something here
            //fire an event for task is started
            $pid = $task->getPid();

            if ($pid !== null) {
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
     * @todo fix here
     * Checks the task whether it has finished.
     *
     * @param int|null $pid
     * @param TaskInterface $task
     */
    private function checkRunningTask(?int $pid, TaskInterface $task): void
    {
        $this->checkTaskTimeout($task);
        if (!$task->getCommand()->isRunning()) {
            //fire an event for task is finished
            if ($pid !== null) {
                $this->runningTasks->remove($pid);
            }
            $this->executeNextPendingTask();
        }
    }

    /**
     * @todo fix here
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
            //fire an event for task is timeout
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
