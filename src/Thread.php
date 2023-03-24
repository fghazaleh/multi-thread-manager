<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use Symfony\Component\Process\Process;
class Thread implements ThreadInterface
{
    private Process $command;

    private ?array $context;

    public function __construct(Process $command, array $context = null)
    {
        $this->command = $command;
        $this->context = $context;
    }

    public static function createFromCommand(string $command, array $context = null): ThreadInterface
    {
        return new static(Process::fromShellCommandline($command), $context);
    }

    public function getSymfonyProcess(): Process
    {
        return $this->command;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function start(): void
    {
        $this->getSymfonyProcess()->start();
    }

    public function stop(): ?int
    {
        return $this->getSymfonyProcess()->stop();
    }

    /**
     * @inheritDoc
     */
    public function getPid(): ?int
    {
        return $this->getSymfonyProcess()->getPid();
    }
}
