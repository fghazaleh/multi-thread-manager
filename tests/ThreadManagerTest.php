<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ThreadManagerTest extends TestCase
{
    #[Test]
    public function itShouldCreateThreadManagerInstanceFromStaticMethod()
    {
        $threadManager = ThreadManager::create(10);
        $this->assertInstanceOf(ThreadManagerInterface::class, $threadManager);
    }

    #[Test]
    public function itShouldCreateThreadsManagerInstance(): void
    {
        $threadManager = new ThreadManager(
            new ThreadSettings(10, 0, 0)
        );
        $this->assertInstanceOf(ThreadManagerInterface::class, $threadManager);
    }

    #[Test]
    public function itShouldAbleToAddShellScriptCommand(): void
    {
        $testingCommand = 'php -r "echo 1;usleep(500);exit(0);"';

        $threadManager = ThreadManager::create(1);

        $threadManager->listen(Event::Finished, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $threadManager->addThread($testingCommand);
        $threadManager->wait();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    #[Test]
    public function itShouldAbleToAddSymfonyProcess(): void
    {
        $testingCommand = 'php -r "echo \'FRANCO\';usleep(500);exit(0);"';

        $threadManager = ThreadManager::create(1);

        $threadManager->listen(Event::Finished, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame('FRANCO', $process->getOutput());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $threadManager->addThread(Process::fromShellCommandline($testingCommand));
        $threadManager->wait();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    #[Test]
    public function itShouldAbleToAddCommandWithContext(): void
    {
        $testingCommand = 'php -r "echo 1;usleep(500);exit(0);"';
        $testingContext = ['data' => 123];

        $threadManager = ThreadManager::create(1);

        $threadManager->listen(Event::Finished, function (ThreadInterface $thread) use ($testingCommand, $testingContext) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame($testingCommand, $process->getCommandLine());
            $this->assertArrayHasKey('data', $thread->getContext());
            $this->assertSame(123, $thread->getContext()['data']);
        });

        $threadManager->addThread($testingCommand, $testingContext);
        $threadManager->wait();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    #[Test]
    public function itShouldAbleToAddThreadClass(): void
    {
        $testingCommand = 'php -r "echo 1;usleep(500);exit(0);"';
        $thread = new class($testingCommand) extends Thread {
            public function __construct(string $command)
            {
                parent::__construct(Process::fromShellCommandline($command), []);
            }
        };

        $threadManager = ThreadManager::create(1);

        //assertion
        $threadManager->listen(Event::Finished, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $threadManager->addThread($thread);
        $threadManager->wait();
        //assertion
        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    #[Test]
    public function itShouldStopTimedOutThreadAndNotFireFinishedEvent(): void
    {
        $threadManager = ThreadManager::create(1);

        $process = Process::fromShellCommandline('exec php -r "sleep(500);"');
        $process->setTimeout(0.5);

        $timeouts = 0;
        $threadManager->listen(Event::Timeout, function (ThreadInterface $thread) use (&$timeouts) {
            $timeouts++;
            $this->assertTrue($thread->getSymfonyProcess()->isTerminated());
        });
        $threadManager->listen(Event::Finished, function () {
            $this->fail('Finished event must not fire for a timed out thread.');
        });

        $threadManager->addThread($process);
        $threadManager->wait();

        $this->assertSame(1, $timeouts);
        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    #[Test]
    public function itShouldStartNextPendingThreadAfterATimeout(): void
    {
        $threadManager = ThreadManager::create(1);

        $slow = Process::fromShellCommandline('exec php -r "sleep(500);"');
        $slow->setTimeout(0.3);

        $finished = [];
        $threadManager->listen(Event::Finished, function (ThreadInterface $thread) use (&$finished) {
            $finished[] = $thread->getContext()['name'];
        });

        $threadManager->addThread($slow, ['name' => 'slow']);
        $threadManager->addThread('php -r "exit(0);"', ['name' => 'fast']);
        $threadManager->wait();

        $this->assertSame(['fast'], $finished);
    }

    #[Test]
    public function itShouldCallMultipleListenersOfTheSameEvent(): void
    {
        $threadManager = ThreadManager::create(1);
        $count = 0;

        $threadManager->listen(Event::Finished, function () use (&$count) {
            $count++;
        });
        $threadManager->listen(Event::Finished, function () use (&$count) {
            $count++;
        });

        $threadManager->addThread('php -r "exit(0);"');
        $threadManager->wait();

        $this->assertSame(2, $count);
    }

    #[Test]
    public function itShouldReturnResultsFromWait(): void
    {
        $threadManager = ThreadManager::create(2);

        $threadManager->addThread('php -r "echo \'ok\'; exit(0);"', ['name' => 'a']);
        $threadManager->addThread('php -r "fwrite(STDERR, \'boom\'); exit(3);"', ['name' => 'b']);
        $results = $threadManager->wait();

        $this->assertCount(2, $results);
        $byName = [];
        foreach ($results as $result) {
            $byName[$result->getContext()['name']] = $result;
        }

        $this->assertSame(ThreadStatus::Succeeded, $byName['a']->status);
        $this->assertTrue($byName['a']->isSuccessful());
        $this->assertSame(0, $byName['a']->exitCode);
        $this->assertSame('ok', $byName['a']->output);
        $this->assertGreaterThan(0.0, $byName['a']->duration);

        $this->assertSame(ThreadStatus::Failed, $byName['b']->status);
        $this->assertFalse($byName['b']->isSuccessful());
        $this->assertSame(3, $byName['b']->exitCode);
        $this->assertSame('boom', $byName['b']->errorOutput);

        $this->assertSame($results, $threadManager->getResults());
    }

    #[Test]
    public function itShouldFireFailedInsteadOfFinishedForNonZeroExit(): void
    {
        $threadManager = ThreadManager::create(1);
        $fired = [];

        foreach ([Event::Finished, Event::Failed, Event::Timeout] as $event) {
            $threadManager->listen($event, function () use (&$fired, $event) {
                $fired[] = $event;
            });
        }

        $threadManager->addThread('php -r "exit(1);"');
        $threadManager->wait();

        $this->assertSame([Event::Failed], $fired);
    }

    #[Test]
    public function itShouldReportTimedOutResult(): void
    {
        $threadManager = ThreadManager::create(1);
        $process = Process::fromShellCommandline('exec php -r "sleep(500);"');
        $process->setTimeout(0.3);

        $threadManager->addThread($process);
        $results = $threadManager->wait();

        $this->assertCount(1, $results);
        $this->assertSame(ThreadStatus::TimedOut, $results[0]->status);
    }

    #[Test]
    public function itShouldStopEverythingOnFirstFailureWhenFailFast(): void
    {
        $threadManager = ThreadManager::create(1, failFast: true);

        $threadManager->addThread('php -r "exit(1);"');
        $threadManager->addThread('php -r "exit(0);"');
        $threadManager->addThread('php -r "exit(0);"');
        $results = $threadManager->wait();

        $this->assertCount(1, $results);
        $this->assertSame(ThreadStatus::Failed, $results[0]->status);
        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    #[Test]
    public function itShouldRunEverythingOnFailureWhenNotFailFast(): void
    {
        $threadManager = ThreadManager::create(1);

        $threadManager->addThread('php -r "exit(1);"');
        $threadManager->addThread('php -r "exit(0);"');

        $this->assertCount(2, $threadManager->wait());
    }

    #[Test]
    public function itShouldAbleToTerminateAllThreads(): void
    {
        $threadManager = ThreadManager::create(2);
        $threadManager->addThread('php -r "echo 1;usleep(4000);exit(0);"');
        $threadManager->addThread('php -r "echo 2;usleep(1000);exit(0);"');

        $this->assertTrue($threadManager->hasUnfinishedThreads());

        $threadManager->terminate();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }
}
