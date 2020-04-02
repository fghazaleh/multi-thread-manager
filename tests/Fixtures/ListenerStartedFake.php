<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Fixtures;

use FGhazaleh\MultiThreadManager\Contracts\ListenerInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use PHPUnit\Framework\Assert;

final class ListenerStartedFake implements ListenerInterface
{
    /**
     * @var ThreadInterface
     */
    private $task = null;

    /**
     * @inheritDoc
     */
    public function handle(ThreadInterface $task): void
    {
        $this->task = $task;
    }

    public function assertHandled():void
    {
        Assert::assertInstanceOf(ThreadInterface::class, $this->task);
    }

    public function assertPid(int $pid):void
    {
        Assert::assertSame($pid, $this->task->getPid());
    }
}
