<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Events;

use FGhazaleh\MultiProcessManager\Contracts\EventInterface;
use FGhazaleh\MultiProcessManager\Contracts\ListenerInterface;
use FGhazaleh\MultiProcessManager\Contracts\TaskInterface;

final class EventContainer implements EventInterface
{
    private $events = [];

    /**
     * @inheritDoc
     */
    public function addListener(string $event, $listener): void
    {
        $this->throwExceptionIfInvalid($event);

        if (!$listener instanceof ListenerInterface && !is_callable($listener)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Listener should be instance of ListenerInterface or callable function.'
                )
            );
        }

        $this->events[$event] = $listener;
    }

    /**
     * @inheritDoc
     * */
    public function fire(string $event, TaskInterface $task): void
    {
        $this->throwExceptionIfInvalid($event);

        if (!isset($this->events[$event])) {
            return;
        }

        $listener = $this->events[$event];
        if (\is_callable($listener)) {
            $listener($task);
        } elseif ($listener instanceof ListenerInterface) {
            $listener->handle($task);
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

    private function throwExceptionIfInvalid(string $event): void
    {
        if (!\array_key_exists($event, $this->getSupportedEvents())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid event [%s].', $event)
            );
        }
    }
}
