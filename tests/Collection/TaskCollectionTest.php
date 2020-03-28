<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Collection;

use FGhazaleh\MultiProcessManager\Contracts\TaskInterface;
use PHPUnit\Framework\TestCase;

class TaskCollectionTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldAbleTaskToPushToCollection()
    {
        $taskCollection = new TaskCollection();

        $taskMock = \Mockery::mock(TaskInterface::class);

        $taskCollection->push($taskMock);

        $this->assertCount(1, $taskCollection);
    }

    /**
     * @test
     */
    public function itShouldAbleToPullTaskFromCollection()
    {
        $taskCollection = new TaskCollection();

        $taskMock = \Mockery::mock(TaskInterface::class);

        $taskCollection->push($taskMock);

        $this->assertCount(1, $taskCollection);

        $taskCollection->pull();

        $this->assertCount(0, $taskCollection);
    }

    /**
     * @test
     */
    public function itShouldAbleToRemoveTaskFromCollection()
    {
        $taskCollection = new TaskCollection();

        $taskMock1 = \Mockery::mock(TaskInterface::class);
        $taskMock2 = \Mockery::mock(TaskInterface::class);
        $pid = 1234;

        $taskCollection->push($taskMock1, $pid);
        $taskCollection->push($taskMock2, 222);

        $this->assertCount(2, $taskCollection);

        $taskCollection->remove($pid);

        $this->assertCount(1, $taskCollection);

        $taskResult = $taskCollection->pull();

        $this->assertSame($taskResult, $taskMock2);
    }

    /**
     * @test
     */
    public function itShouldClearTaskCollection()
    {
        $taskCollection = new TaskCollection();
        $taskMock1 = \Mockery::mock(TaskInterface::class);

        $taskCollection->push($taskMock1);

        $this->assertCount(1, $taskCollection);

        $taskCollection->clear();

        $this->assertCount(0, $taskCollection);
    }

    /**
     * @test
     */
    public function itShouldAbleToIterateToCollection()
    {
        $taskCollection = new TaskCollection();
        $taskMock1 = \Mockery::mock(TaskInterface::class);
        $taskCollection->push($taskMock1);

        foreach ($taskCollection as $item) {
            $this->assertSame($taskMock1, $item);
        }
    }
}
