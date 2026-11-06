<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Collection;

use ArrayIterator;
use Countable;
use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use IteratorAggregate;
use Override;
use RuntimeException;

/**
 * @implements IteratorAggregate<int, ThreadInterface>
 */
final class ThreadCollection implements IteratorAggregate, Countable
{
    /** @var array<int, ThreadInterface> */
    private array $collection = [];

    /**
     * Pushes a thread to the queue, indexed by its pid when given.
     */
    public function push(ThreadInterface $task, ?int $pid = null): self
    {
        if ($pid === null) {
            $this->collection[] = $task;
        } else {
            $this->collection[$pid] = $task;
        }

        return $this;
    }

    public function remove(int $pid): void
    {
        unset($this->collection[$pid]);
    }

    public function clear(): void
    {
        $this->collection = [];
    }

    /**
     * Pulls the first thread from the queue.
     *
     * @throws RuntimeException when the collection is empty
     */
    public function pull(): ThreadInterface
    {
        return array_shift($this->collection)
            ?? throw new RuntimeException('Cannot pull a thread from an empty collection.');
    }

    #[Override]
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * @return ArrayIterator<int, ThreadInterface>
     */
    #[Override]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->collection);
    }
}
