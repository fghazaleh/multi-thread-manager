<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

use Symfony\Component\Process\Process;

interface ThreadManagerInterface
{
    /**
     * Adds Symfony process, shell script command or Thread instance to thread manager.
     *
     * @param array<string, mixed>|null $context
     */
    public function addThread(string|Process|ThreadInterface $command, ?array $context = null): void;

    /**
     * Wait for all symfony process to finish.
     */
    public function wait(): void;

    /**
     * Terminate pending and running threads.
     */
    public function terminate(): void;
}
