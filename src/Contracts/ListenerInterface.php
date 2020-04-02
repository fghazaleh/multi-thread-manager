<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

interface ListenerInterface
{
    /**
     * Handles the received event.
     *
     * @param ThreadInterface $task
     *
     * @return void
     */
    public function handle(ThreadInterface $task): void;
}
