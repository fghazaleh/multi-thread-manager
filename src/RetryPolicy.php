<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Exception\InvalidSettingsException;

/**
 * Describes how often and how quickly a failed or timed out thread is retried.
 *
 * The delay before retry N (starting at 1) is delay * multiplier^(N - 1) milliseconds.
 */
final readonly class RetryPolicy
{
    /**
     * @param int $maxRetries Retries after the first attempt (at least 1).
     * @param int $delay Delay in milliseconds before the first retry.
     * @param float $multiplier Backoff multiplier applied for every further retry (1.0 = constant delay).
     * @throws InvalidSettingsException
     */
    public function __construct(
        public int $maxRetries,
        public int $delay = 0,
        public float $multiplier = 1.0,
    ) {
        if ($maxRetries < 1) {
            throw new InvalidSettingsException('Max retries must be at least 1.');
        }
        if ($delay < 0) {
            throw new InvalidSettingsException('Retry delay must not be negative.');
        }
        if ($multiplier < 1.0) {
            throw new InvalidSettingsException('Retry multiplier must be at least 1.0.');
        }
    }

    /**
     * Delay in milliseconds before the given retry (1 = first retry).
     */
    public function delayFor(int $retry): int
    {
        return (int) round($this->delay * ($this->multiplier ** ($retry - 1)));
    }
}
