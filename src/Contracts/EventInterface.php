<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

use FGhazaleh\MultiThreadManager\Exception\InvalidEventArgumentException;
use FGhazaleh\MultiThreadManager\Exception\InvalidListenerArgumentException;

interface EventInterface
{
    const EVENT_STARTED = 'event_started';
    const EVENT_FINISHED = 'event_finished';
    const EVENT_TIMEOUT = 'event_timeout';

    /**
     * @param string $event
     * @param callable|ListenerInterface $listener
     *@throws InvalidEventArgumentException
     * @throws InvalidListenerArgumentException
     * @return void
     */
    public function addListener(string $event, $listener): void;


    /**
     * @param string $event
     * @param ThreadInterface $thread
     *@throws InvalidEventArgumentException
     *@return void
     */
    public function fire(string $event, ThreadInterface $thread): void;
}
