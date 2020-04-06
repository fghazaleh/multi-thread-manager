<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\EventInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerInterface;
use FGhazaleh\MultiThreadManager\Exception\InvalidThreadException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ThreadManagerTest extends TestCase
{

    /**
     * @test
     */
    public function itShouldCreateThreadManagerInstanceFromStaticMethod()
    {
        $threadManager = ThreadManager::create(10);
        $this->assertInstanceOf(ThreadManagerInterface::class, $threadManager);
    }

    /**
     * @test
     */
    public function itShouldCreateThreadsManagerInstance()
    {
        $threadManager = new ThreadManager(
            new ThreadSettings(10, 0, 0)
        );
        $this->assertInstanceOf(ThreadManagerInterface::class, $threadManager);
    }

    /**
     * @test
     */
    public function itShouldAbleToAddShellScriptCommand()
    {
        $testingCommand = 'php -r "echo 1;usleep(500);exit(0);"';

        $threadManager = ThreadManager::create(1);

        $threadManager->listen(EventInterface::EVENT_FINISHED, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $threadManager->addThread($testingCommand);
        $threadManager->wait();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    /**
     * @test
     */
    public function itShouldAbleToAddSymfonyProcess()
    {
        $testingCommand = 'php -r "echo \'FRANCO\';usleep(500);exit(0);"';

        $threadManager = ThreadManager::create(1);

        $threadManager->listen(EventInterface::EVENT_FINISHED, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame('FRANCO', $process->getOutput());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $threadManager->addThread(Process::fromShellCommandline($testingCommand));
        $threadManager->wait();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    /**
     * @test
     */
    public function itShouldAbleToAddCommandWithContext()
    {
        $testingCommand = 'php -r "echo 1;usleep(500);exit(0);"';
        $testingContext = ['data' => 123];

        $threadManager = ThreadManager::create(1);

        $threadManager->listen(EventInterface::EVENT_FINISHED, function (ThreadInterface $thread) use ($testingCommand, $testingContext) {
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

    /**
     * @test
     */
    public function itShouldAbleToAddThreadClass()
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
        $threadManager->listen(EventInterface::EVENT_FINISHED, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $threadManager->addThread($thread);
        $threadManager->wait();
        //assertion
        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }
    /**
     * @test
     */
    public function itShouldThrowInvalidThreadExceptionWhenAddingInvalidThreadType()
    {
        $this->expectException(InvalidThreadException::class);
        $this->expectExceptionMessage('Invalid thread type.');

        $threadManager = ThreadManager::create(1);
        $threadManager->addThread(new \stdClass());
    }

    /**
     * @test
     */
    public function itShouldFireTimeoutEventWhenThreadIsTimeout()
    {
        $testingCommand = 'php -r "echo 1;sleep(500);exit(0);"';
        $threadManager = ThreadManager::create(1);

        $process = Process::fromShellCommandline($testingCommand, null, null);
        $process->setTimeout(0.5);

        $threadManager->listen(EventInterface::EVENT_TIMEOUT, function (ThreadInterface $thread) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(143, $process->getExitCode());
            $this->assertTrue($process->isTerminated());
        });

        $threadManager->addThread($process);
        $threadManager->wait();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }

    /**
     * @test
     */
    public function itShouldAbleToTerminateAllThreads()
    {
        $threadManager = ThreadManager::create(2);
        $threadManager->addThread('php -r "echo 1;usleep(4000);exit(0);"');
        $threadManager->addThread('php -r "echo 2;usleep(1000);exit(0);"');

        $this->assertTrue($threadManager->hasUnfinishedThreads());

        $threadManager->terminate();

        $this->assertFalse($threadManager->hasUnfinishedThreads());
    }
}
