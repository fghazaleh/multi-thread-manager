<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Fixtures;

use FGhazaleh\MultiThreadManager\Contracts\ListenerInterface;
use FGhazaleh\MultiThreadManager\Contracts\TaskInterface;
use PHPUnit\Framework\Assert;

final class ListenerStartedFake implements ListenerInterface
{
    /**
     * @var TaskInterface
     */
    private $task = null;

    /**
     * @inheritDoc
     */
    public function handle(TaskInterface $task): void
    {
        $this->task = $task;
    }

    public function assertHandled():void
    {
        Assert::assertInstanceOf(TaskInterface::class, $this->task);
    }

    public function assertPid(int $pid):void
    {
        Assert::assertSame($pid, $this->task->getPid());
    }
}
