<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contacts;

use Symfony\Component\Process\Process;

interface ProcessManagerInterface
{
    /**
     * Adds Symfony process, shell script command or Task instance to process manager.
     *
     * @param Process|string|TaskInterface $command
     * @param array|null $context
     * @return void
     */
    public function add($command, array $context = null): void ;

    /**
     * Wait for all symfony process to finish.
     */
    public function wait(): void;
}
