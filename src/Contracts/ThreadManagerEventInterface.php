<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

use FGhazaleh\MultiThreadManager\Event;

interface ThreadManagerEventInterface
{
    /**
     * @param callable(ThreadInterface): mixed|ListenerInterface $listener
     */
    public function listen(Event $event, callable|ListenerInterface $listener): void;
}
