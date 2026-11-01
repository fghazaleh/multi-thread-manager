<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

use FGhazaleh\MultiThreadManager\RetryPolicy;
use FGhazaleh\MultiThreadManager\ThreadResult;
use Symfony\Component\Process\Process;

interface ThreadManagerInterface
{
    /**
     * Adds Symfony process, shell script command or Thread instance to thread manager.
     *
     * @param array<string, mixed>|null $context
     * @param RetryPolicy|null $retry Retries the thread when it fails or times out.
     */
    public function addThread(string|Process|ThreadInterface $command, ?array $context = null, ?RetryPolicy $retry = null): void;

    /**
     * Wait for all threads to finish and return the results of every thread
     * that ran to completion, failed or timed out, in completion order.
     *
     * @return list<ThreadResult>
     */
    public function wait(): array;

    /**
     * Returns the results collected so far.
     *
     * @return list<ThreadResult>
     */
    public function getResults(): array;

    /**
     * Terminate pending and running threads.
     */
    public function terminate(): void;
}
