<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

use Symfony\Component\Process\Process;

interface ThreadInterface
{
    public function start(): void;

    public function stop(): ?int;

    /**
     * Starts a fresh attempt of an already finished thread (used for retries).
     */
    public function restart(): void;

    public function getPid(): ?int;

    public function getSymfonyProcess(): Process;

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array;
}
