<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contacts;

use Symfony\Component\Process\Process;

interface TaskInterface
{
    /**
     * @return Process
     */
    public function getCommand(): Process;

    /**
     * @return array|null
     */
    public function getContext(): ?array;
}
