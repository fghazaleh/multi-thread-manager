<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Fixtures;

use FGhazaleh\MultiProcessManager\Contracts\ListenerInterface;
use FGhazaleh\MultiProcessManager\Contracts\TaskInterface;
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
