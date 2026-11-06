<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Events;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use FGhazaleh\MultiThreadManager\Event;
use FGhazaleh\MultiThreadManager\Fixtures\ListenerStartedFake;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventManagerTest extends TestCase
{
    #[Test]
    public function itShouldCallAllListenersOfAnEventInOrder(): void
    {
        $events = new EventManager();
        $calls = [];

        $events->addListener(Event::Finished, function () use (&$calls) {
            $calls[] = 'first';
        });
        $events->addListener(Event::Finished, function () use (&$calls) {
            $calls[] = 'second';
        });
        $events->addListener(Event::Started, function () use (&$calls) {
            $calls[] = 'started';
        });

        $events->fire(Event::Finished, \Mockery::mock(ThreadInterface::class));

        $this->assertSame(['first', 'second'], $calls);
    }

    #[Test]
    public function itShouldDoNothingWhenEventHasNoListeners(): void
    {
        $events = new EventManager();

        $events->fire(Event::Timeout, \Mockery::mock(ThreadInterface::class));

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function itShouldFireEventStartedWithCallableListener(): void
    {
        $events = new EventManager();

        $taskFake = \Mockery::mock(ThreadInterface::class);
        $taskFake->shouldReceive('getPid')->andReturn(123);

        $events->addListener(Event::Started, function (ThreadInterface $task) {
            $this->assertSame(123, $task->getPid());
        });

        $events->fire(Event::Started, $taskFake);
    }

    #[Test]
    public function itShouldFireEventStartedWithClassListener(): void
    {
        $events = new EventManager();

        $taskFake = \Mockery::mock(ThreadInterface::class);
        $taskFake->shouldReceive('getPid')->andReturn(123);

        $startedFakeListener = new ListenerStartedFake();

        $events->addListener(Event::Started, $startedFakeListener);

        $events->fire(Event::Started, $taskFake);

        $startedFakeListener->assertHandled();
        $startedFakeListener->assertPid(123);
    }
}
