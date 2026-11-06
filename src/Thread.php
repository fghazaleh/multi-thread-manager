<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use Override;
use Symfony\Component\Process\Process;

class Thread implements ThreadInterface
{
    /**
     * @param array<string, mixed>|null $context
     */
    public function __construct(
        private readonly Process $command,
        private readonly ?array $context = null,
    ) {
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public static function createFromCommand(string $command, ?array $context = null): static
    {
        return new static(Process::fromShellCommandline($command), $context);
    }

    #[Override]
    public function getSymfonyProcess(): Process
    {
        return $this->command;
    }

    #[Override]
    public function getContext(): ?array
    {
        return $this->context;
    }

    #[Override]
    public function start(): void
    {
        $this->command->start();
    }

    #[Override]
    public function stop(): ?int
    {
        return $this->command->stop();
    }

    #[Override]
    public function getPid(): ?int
    {
        return $this->command->getPid();
    }
}
