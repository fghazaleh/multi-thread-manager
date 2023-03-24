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
use FGhazaleh\MultiThreadManager\Exception\InvalidEventArgumentException;
use FGhazaleh\MultiThreadManager\Exception\InvalidListenerArgumentException;

final class EventManager implements EventInterface
{
    private $events = [];

    /**
     * @inheritDoc
     */
    public function addListener(string $event, $listener): void
    {
        $this->throwExceptionIfInvalid($event);

        if (!$listener instanceof ListenerInterface && !is_callable($listener)) {
            throw new InvalidListenerArgumentException;
        }

        $this->events[$event] = $listener;
    }

    /**
     * @inheritDoc
     * */
    public function fire(string $event, ThreadInterface $thread): void
    {
        $this->throwExceptionIfInvalid($event);

        if (!isset($this->events[$event])) {
            return;
        }

        $listener = $this->events[$event];
        if (is_callable($listener)) {
            $listener($thread);
        } elseif ($listener instanceof ListenerInterface) {
            $listener->handle($thread);
        }
    }

    private function getSupportedEvents(): array
    {
        return [
            EventInterface::EVENT_STARTED => true,
            EventInterface::EVENT_FINISHED => true,
            EventInterface::EVENT_TIMEOUT => true,
        ];
    }

    /**
     * @param string $event
     * @throws InvalidEventArgumentException
     */
    private function throwExceptionIfInvalid(string $event): void
    {
        if (!array_key_exists($event, $this->getSupportedEvents())) {
            throw new InvalidEventArgumentException(
                sprintf('Invalid event [%s].', $event)
            );
        }
    }
}
