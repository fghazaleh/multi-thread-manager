<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Events;

use FGhazaleh\MultiThreadManager\Contracts\EventInterface;
use FGhazaleh\MultiThreadManager\Contracts\ListenerInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use FGhazaleh\MultiThreadManager\Event;
use Override;

final class EventManager implements EventInterface
{
    /** @var array<string, list<callable|ListenerInterface>> */
    private array $listeners = [];

    #[Override]
    public function addListener(Event $event, callable|ListenerInterface $listener): void
    {
        $this->listeners[$event->value][] = $listener;
    }

    #[Override]
    public function fire(Event $event, ThreadInterface $thread): void
    {
        foreach ($this->listeners[$event->value] ?? [] as $listener) {
            if ($listener instanceof ListenerInterface) {
                $listener->handle($thread);
            } else {
                $listener($thread);
            }
        }
    }
}
