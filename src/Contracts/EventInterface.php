<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contracts;

interface EventInterface
{
    const EVENT_STARTED = 'event_started';
    const EVENT_FINISHED = 'event_finished';
    const EVENT_TIMEOUT = 'event_timeout';

    /**
     * @param string $event
     * @param callable|ListenerInterface $listener
     * @throws \InvalidArgumentException
     * @return void
     */
    public function addListener(string $event, $listener): void;


    /**
     * @param string $event
     * @param TaskInterface $task
     * @throws \InvalidArgumentException
     * @return void
     */
    public function fire(string $event, TaskInterface $task): void;
}
