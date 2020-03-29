<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contracts;

interface ListenerInterface
{
    /**
     * Handles the received event.
     *
     * @param TaskInterface $task
     *
     * @return void
     */
    public function handle(TaskInterface $task): void;
}
