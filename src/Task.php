<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use FGhazaleh\MultiProcessManager\Contacts\TaskInterface;
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

    /**
     * @inheritDoc
     */
    public function getPid(): ?int
    {
        return  $this->getCommand()->getPid();
    }
}
