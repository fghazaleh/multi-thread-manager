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
     * @param Process|string|ThreadInterface $command
     * @param array|null $context
     * @return void
     */
    public function addThread($command, array $context = null): void ;

    /**
     * Wait for all symfony process to finish.
     */
    public function wait(): void;
}
