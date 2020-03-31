<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Events;

use FGhazaleh\MultiProcessManager\Contracts\EventInterface;
use FGhazaleh\MultiProcessManager\Contracts\TaskInterface;
use FGhazaleh\MultiProcessManager\Exception\InvalidEventArgumentExeption;
use FGhazaleh\MultiProcessManager\Exception\InvalidListenerArgumentException;
use FGhazaleh\MultiProcessManager\Fixtures\ListenerStartedFake;
use PHPUnit\Framework\TestCase;

class EventContainerTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldAbleToAddEventsAndCallableMethodListeners()
    {
        $events = new EventContainer();

        $events->addListener(EventInterface::EVENT_STARTED, function () {
        });
        $events->addListener(EventInterface::EVENT_FINISHED, function () {
        });
        $events->addListener(EventInterface::EVENT_FINISHED, function () {
        });

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function itShouldThrowInvalidEventExceptionWhenAddInvalidEvent()
    {
        $this->expectException(InvalidEventArgumentExeption::class);
        $this->expectExceptionMessage('Invalid event [fake_event].');

        $events = new EventContainer();

        $events->addListener('fake_event', function () {
        });
    }

    /**
     * @test
     */
    public function itShouldThrowInvalidListenerExceptionWhenAddInvalidListener()
    {
        $this->expectException(InvalidListenerArgumentException::class);
        $this->expectExceptionMessage('Listener should be instance of ListenerInterface or callable function.');

        $events = new EventContainer();

        $events->addListener(EventInterface::EVENT_STARTED, 'fake-listener');
    }

    /**
     * @test
     */
    public function itShouldFireEventStartedWithCallableListener()
    {
        $events = new EventContainer();

        $taskFake = \Mockery::mock(TaskInterface::class);
        $taskFake->shouldReceive('getPid')->andReturn(123);

        $events->addListener(EventInterface::EVENT_STARTED, function (TaskInterface $task) {
            $this->assertSame(123, $task->getPid());
        });

        $events->fire(EventInterface::EVENT_STARTED, $taskFake);
    }

    /**
     * @test
     */
    public function itShouldFireEventStartedWithClassListener()
    {
        $events = new EventContainer();

        $taskFake = \Mockery::mock(TaskInterface::class);
        $taskFake->shouldReceive('getPid')->andReturn(123);

        $startedFakeListener = new ListenerStartedFake();

        $events->addListener(EventInterface::EVENT_STARTED, $startedFakeListener);

        $events->fire(EventInterface::EVENT_STARTED, $taskFake);

        $startedFakeListener->assertHandled();
        $startedFakeListener->assertPid(123);
    }
}
