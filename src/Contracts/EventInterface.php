<?php

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
     * @return void
     */
    public function addListener(string $event, $listener): void;
}
