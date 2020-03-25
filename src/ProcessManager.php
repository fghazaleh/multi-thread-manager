<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use FGhazaleh\MultiProcessManager\Contacts\ProcessManagerInterface;
use FGhazaleh\MultiProcessManager\Contacts\TaskInterface;
use Symfony\Component\Process\Process;

final class ProcessManager implements ProcessManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function add($command, array $context = null): ProcessManagerInterface
    {
        $task = $this->createTask($command, $context);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function wait(): void
    {
        // TODO: Implement wait() method.
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
}
