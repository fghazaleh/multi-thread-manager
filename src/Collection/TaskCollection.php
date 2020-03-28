<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Collection;

use FGhazaleh\MultiProcessManager\Contracts\TaskInterface;

final class TaskCollection implements \IteratorAggregate, \Countable
{
    private $collection = [];

    /**
     * Push task to the queue.
     *
     * @param TaskInterface $task
     * @param int|null $pid
     * @return TaskCollection
     */
    public function push(TaskInterface $task, ?int $pid = null): TaskCollection
    {
        if ($pid === null) {
            array_push($this->collection, $task);
        } else {
            $this->collection[$pid] = $task;
        }

        return $this;
    }

    /**
     * Remove the task from the collection if the pid exists.
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
     * Pulls the first task from the queue.
     *
     * @return TaskInterface
     */
    public function pull(): TaskInterface
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