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
     * Returns the process pid
     *
     * @return null|int;
     */
    public function getPid(): ?int;

    public function getSymfonyProcess(): Process;

    /**
     * @return array|null
     */
    public function getContext(): ?array;
}
