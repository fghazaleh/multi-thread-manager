<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadSettingsInterface;
use FGhazaleh\MultiThreadManager\Exception\InvalidSettingsException;
use Override;

final readonly class ThreadSettings implements ThreadSettingsInterface
{
    /**
     * @param int $threadSize Maximum number of threads running in parallel (at least 1).
     * @param int $threadStartDelay Delay in milliseconds before a thread is started.
     * @param int $pollInterval Interval in milliseconds between two checks of the running threads.
     * @throws InvalidSettingsException
     */
    public function __construct(
        private int $threadSize,
        private int $threadStartDelay,
        private int $pollInterval,
    ) {
        if ($threadSize < 1) {
            throw new InvalidSettingsException('Thread size must be at least 1.');
        }
        if ($threadStartDelay < 0) {
            throw new InvalidSettingsException('Thread start delay must not be negative.');
        }
        if ($pollInterval < 0) {
            throw new InvalidSettingsException('Poll interval must not be negative.');
        }
    }

    public static function createFromDefault(): self
    {
        return new self(threadSize: 8, threadStartDelay: 1, pollInterval: 1);
    }

    #[Override]
    public function getThreadSize(): int
    {
        return $this->threadSize;
    }

    #[Override]
    public function getThreadStartDelay(): int
    {
        return $this->threadStartDelay;
    }

    #[Override]
    public function getPollInterval(): int
    {
        return $this->pollInterval;
    }
}
