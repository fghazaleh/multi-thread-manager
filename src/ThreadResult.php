<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;

/**
 * The outcome of a thread that ran to completion, failed or timed out.
 */
final readonly class ThreadResult
{
    public function __construct(
        public ThreadInterface $thread,
        public ThreadStatus $status,
        public ?int $exitCode,
        public string $output,
        public string $errorOutput,
        public float $duration,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->status === ThreadStatus::Succeeded;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->thread->getContext();
    }
}
