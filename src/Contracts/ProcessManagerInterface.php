<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contacts;

use Symfony\Component\Process\Process;

interface ProcessManagerInterface
{
    /**
     * @param Process|string|TaskInterface $command
     * @param array|null $context
     * @return ProcessManagerInterface
     */
    public function add($command, array $context = null): ProcessManagerInterface;

    /**
     * Wait for all symfony process to finish.
     */
    public function wait(): void;
}
