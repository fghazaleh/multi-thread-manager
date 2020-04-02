<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Collection;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;

final class ThreadCollection implements \IteratorAggregate, \Countable
{
    private $collection = [];

    /**
     * Push thread to the queue.
     *
     * @param ThreadInterface $task
     * @param int|null $pid
     * @return ThreadCollection
     */
    public function push(ThreadInterface $task, ?int $pid = null): ThreadCollection
    {
        if ($pid === null) {
            array_push($this->collection, $task);
        } else {
            $this->collection[$pid] = $task;
        }

        return $this;
    }

    /**
     * Remove the thread from the collection if the pid exists.
     *
     * @param int $pid
     * @return void
     */
    public function remove(int $pid): void
    {
        if (isset($this->collection[$pid])) {
            unset($this->collection[$pid]);
        }
    }

    public function clear(): void
    {
        $this->collection = [];
    }

    /**
     * Pulls the first thread from the queue.
     *
     * @return ThreadInterface
     */
    public function pull(): ThreadInterface
    {
        return array_shift($this->collection);
    }

    /**
     * Returns the collection size.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }
}
