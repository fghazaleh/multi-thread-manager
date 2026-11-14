<?php

/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ThreadManagerOutputTest extends TestCase
{
    private const COMMAND = 'php -r "echo \'a\'; fwrite(STDERR, \'e\'); usleep(400000); echo \'b\';"';

    #[Test]
    public function itShouldDeliverOutputInChunksWhileTheThreadRuns(): void
    {
        $threadManager = ThreadManager::create(1);
        $chunks = [];
        $threadManager->onOutput(function (ThreadInterface $thread, OutputType $type, string $chunk) use (&$chunks) {
            $chunks[] = [$type, $chunk];
        });

        $threadManager->addThread(self::COMMAND);
        $threadManager->wait();

        $stdout = array_values(array_filter($chunks, fn(array $c) => $c[0] === OutputType::Stdout));
        $stderr = array_values(array_filter($chunks, fn(array $c) => $c[0] === OutputType::Stderr));

        $this->assertSame(['a', 'b'], array_column($stdout, 1));
        $this->assertSame(['e'], array_column($stderr, 1));
    }

    #[Test]
    public function itShouldDeliverTheLastChunkBeforeTheFinishedEvent(): void
    {
        $threadManager = ThreadManager::create(1);
        $order = [];
        $threadManager->onOutput(function () use (&$order) {
            $order[] = 'output';
        });
        $threadManager->listen(Event::Finished, function () use (&$order) {
            $order[] = 'finished';
        });

        $threadManager->addThread('php -r "echo 1;"');
        $threadManager->wait();

        $this->assertSame(['output', 'finished'], $order);
    }

    #[Test]
    public function itShouldNotAffectTheCollectedResultOutput(): void
    {
        $threadManager = ThreadManager::create(1);
        $threadManager->onOutput(function () {});

        $threadManager->addThread(self::COMMAND);
        $results = $threadManager->wait();

        $this->assertSame('ab', $results[0]->output);
        $this->assertSame('e', $results[0]->errorOutput);
    }

    #[Test]
    public function itShouldCallAllOutputListeners(): void
    {
        $threadManager = ThreadManager::create(1);
        $calls = 0;
        $threadManager->onOutput(function () use (&$calls) {
            $calls++;
        });
        $threadManager->onOutput(function () use (&$calls) {
            $calls++;
        });

        $threadManager->addThread('php -r "echo 1;"');
        $threadManager->wait();

        $this->assertSame(2, $calls);
    }
}
