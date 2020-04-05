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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ThreadManagerTest extends TestCase
{

    /**
     * @test
     */
    public function itShouldCreateThreadManagerInstanceFromStaticMethod()
    {
        $processManager = ThreadManager::create(10);
        $this->assertInstanceOf(ThreadManagerInterface::class, $processManager);
    }

    /**
     * @test
     */
    public function itShouldCreateThreadsManagerInstance()
    {
        $processManager = new ThreadManager(
            new ThreadSettings(10, 0, 0)
        );
        $this->assertInstanceOf(ThreadManagerInterface::class, $processManager);
    }

    /**
     * @test
     */
    public function itShouldAbleToAddShellScriptCommand()
    {
        $testingCommand = 'php -r "echo 1;usleep(500);exit(0);"';

        $processManager = ThreadManager::create(1);

        $processManager->listen(EventInterface::EVENT_FINISHED, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $processManager->addThread($testingCommand);
        $processManager->wait();

        $this->assertFalse($processManager->hasUnfinishedThreads());
    }

    /**
     * @test
     */
    public function itShouldAbleToAddSymfonyProcess()
    {
        $testingCommand = 'php -r "echo \'FRANCO\';usleep(500);exit(0);"';

        $processManager = ThreadManager::create(1);

        $processManager->listen(EventInterface::EVENT_FINISHED, function (ThreadInterface $thread) use ($testingCommand) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame('FRANCO', $process->getOutput());
            $this->assertSame($testingCommand, $process->getCommandLine());
        });

        $processManager->addThread(Process::fromShellCommandline($testingCommand));
        $processManager->wait();

        $this->assertFalse($processManager->hasUnfinishedThreads());
    }

    /**
     * @test
     */
    public function itShouldAbleToAddCommandWithContext()
    {
        $testingCommand = 'php -r "echo 1;usleep(500);exit(0);"';
        $testingContext = ['data' => 123];

        $processManager = ThreadManager::create(1);

        $processManager->listen(EventInterface::EVENT_FINISHED, function (ThreadInterface $thread) use ($testingCommand, $testingContext) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(0, $process->getExitCode());
            $this->assertSame($testingCommand, $process->getCommandLine());
            $this->assertArrayHasKey('data', $thread->getContext());
            $this->assertSame(123, $thread->getContext()['data']);
        });

        $processManager->addThread($testingCommand, $testingContext);
        $processManager->wait();

        $this->assertFalse($processManager->hasUnfinishedThreads());
    }

    /**
     * @test
     */
    public function itShouldFireTimeoutEventWhenThreadIsTimeout()
    {
        $testingCommand = 'php -r "echo 1;sleep(500);exit(0);"';
        $processManager = ThreadManager::create(1);

        $process = Process::fromShellCommandline($testingCommand, null, null);
        $process->setTimeout(0.5);

        $processManager->listen(EventInterface::EVENT_TIMEOUT, function (ThreadInterface $thread) {
            $process = $thread->getSymfonyProcess();
            $this->assertSame(143, $process->getExitCode());
            $this->assertTrue($process->isTerminated());
        });

        $processManager->addThread($process);
        $processManager->wait();

        $this->assertFalse($processManager->hasUnfinishedThreads());
    }
}
