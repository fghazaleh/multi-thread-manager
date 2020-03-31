<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contracts;

use Symfony\Component\Process\Process;

interface TaskInterface
{
    public function start(): void;

    public function stop(): ?int;

    /**
     * Returns the process pid
     *
     * @return null|int;
     */
    public function getPid(): ?int;

    /**
     * @return Process
     */
    public function getCommand(): Process;

    /**
     * @return array|null
     */
    public function getContext(): ?array;
}
