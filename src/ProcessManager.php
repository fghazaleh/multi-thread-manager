<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use FGhazaleh\MultiProcessManager\Collection\TaskCollection;
use FGhazaleh\MultiProcessManager\Contacts\ProcessManagerInterface;
use FGhazaleh\MultiProcessManager\Contacts\ProcessSettingsInterface;
use FGhazaleh\MultiProcessManager\Contacts\TaskInterface;
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
    public function add($command, array $context = null): ProcessManagerInterface
    {
        $this->pendingTasks->push(
            $this->createTask($command, $context)
        );
        $this->executeNextPendingTask();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function wait(): void
    {
        // TODO: Implement wait() method.
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
            $task->getCommand()->start();


        }
    }

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
        return $this->runningTasks->count() < $this->processSettings->getThreads() &&
            $this->pendingTasks->count() > 0;
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
