<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use FGhazaleh\MultiProcessManager\Contracts\ProcessManagerInterface;
use PHPUnit\Framework\TestCase;

class ProcessManagerTest extends TestCase
{

    /**
     * @test
     */
    public function itShouldCreateProcessManagerInstanceFromStaticMethod()
    {
        $processManager = ProcessManager::create(10);
        $this->assertInstanceOf(ProcessManagerInterface::class, $processManager);
    }

    /**
     * @test
     */
    public function itShouldCreateProcessManagerInstance()
    {
        $processManager = new ProcessManager(
            new ProcessSettings(10, 0, 0)
        );
        $this->assertInstanceOf(ProcessManagerInterface::class, $processManager);
    }
}
