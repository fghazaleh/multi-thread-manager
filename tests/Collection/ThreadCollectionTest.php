<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Collection;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use PHPUnit\Framework\TestCase;

class ThreadCollectionTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldAbleToPushThreadToCollection()
    {
        $taskCollection = new ThreadCollection();

        $threadMock = \Mockery::mock(ThreadInterface::class);

        $taskCollection->push($threadMock);

        $this->assertCount(1, $taskCollection);
    }

    /**
     * @test
     */
    public function itShouldAbleToPullThreadFromCollection()
    {
        $threadCollection = new ThreadCollection();

        $threadMock = \Mockery::mock(ThreadInterface::class);

        $threadCollection->push($threadMock);

        $this->assertCount(1, $threadCollection);

        $threadCollection->pull();

        $this->assertCount(0, $threadCollection);
    }

    /**
     * @test
     */
    public function itShouldAbleToRemoveTaskFromCollection()
    {
        $threadCollection = new ThreadCollection();

        $threadMock1 = \Mockery::mock(ThreadInterface::class);
        $threadMock2 = \Mockery::mock(ThreadInterface::class);
        $pid = 1234;

        $threadCollection->push($threadMock1, $pid);
        $threadCollection->push($threadMock2, 222);

        $this->assertCount(2, $threadCollection);

        $threadCollection->remove($pid);

        $this->assertCount(1, $threadCollection);

        $taskResult = $threadCollection->pull();

        $this->assertSame($taskResult, $threadMock2);
    }

    /**
     * @test
     */
    public function itShouldClearThreadCollection()
    {
        $threadCollection = new ThreadCollection();
        $threadMock1 = \Mockery::mock(ThreadInterface::class);

        $threadCollection->push($threadMock1);

        $this->assertCount(1, $threadCollection);

        $threadCollection->clear();

        $this->assertCount(0, $threadCollection);
    }

    /**
     * @test
     */
    public function itShouldAbleToIterateToCollection()
    {
        $threadCollection = new ThreadCollection();
        $threadMock1 = \Mockery::mock(ThreadInterface::class);
        $threadCollection->push($threadMock1);

        foreach ($threadCollection as $item) {
            $this->assertSame($threadMock1, $item);
        }
    }
}
