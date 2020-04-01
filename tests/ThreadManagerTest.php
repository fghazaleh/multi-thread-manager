<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadManagerInterface;
use PHPUnit\Framework\TestCase;

class ThreadManagerTest extends TestCase
{

    /**
     * @test
     */
    public function itShouldCreateProcessManagerInstanceFromStaticMethod()
    {
        $processManager = ThreadManager::create(10);
        $this->assertInstanceOf(ThreadManagerInterface::class, $processManager);
    }

    /**
     * @test
     */
    public function itShouldCreateProcessManagerInstance()
    {
        $processManager = new ThreadManager(
            new ThreadSettings(10, 0, 0)
        );
        $this->assertInstanceOf(ThreadManagerInterface::class, $processManager);
    }
}
