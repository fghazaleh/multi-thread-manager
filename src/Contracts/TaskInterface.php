<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contacts;

use Symfony\Component\Process\Process;

interface TaskInterface
{
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
