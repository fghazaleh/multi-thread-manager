<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

use FGhazaleh\MultiThreadManager\Event;

interface EventInterface
{
    /**
     * Adds a listener to the event. An event can have multiple listeners,
     * which are called in the order they were added.
     *
     * @param callable(ThreadInterface): mixed|ListenerInterface $listener
     */
    public function addListener(Event $event, callable|ListenerInterface $listener): void;

    public function fire(Event $event, ThreadInterface $thread): void;
}
