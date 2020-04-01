<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\TaskInterface;
use Symfony\Component\Process\Process;

final class Task implements TaskInterface
{
    /**
     * @var Process
     */
    private $command;
    /**
     * @var array
     */
    private $context;

    public function __construct(Process $command, array $context)
    {
        $this->command = $command;
        $this->context = $context;
    }

    public function getCommand(): Process
    {
        return $this->command;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function start(): void
    {
        $this->getCommand()->start();
    }

    public function stop(): ?int
    {
        return $this->getCommand()->stop();
    }

    /**
     * @inheritDoc
     */
    public function getPid(): ?int
    {
        return $this->getCommand()->getPid();
    }
}
