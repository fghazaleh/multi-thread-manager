<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use FGhazaleh\MultiThreadManager\Contracts\ThreadSettingsInterface;

final class ThreadSettings implements ThreadSettingsInterface
{
    /**
     * @var int
     */
    private $threads;
    /**
     * @var int
     */
    private $threadStartDelay;
    /**
     * @var int
     */
    private $pollInterval;

    public function __construct(int $threads, int $threadStartDelay, int $pollInterval)
    {
        $this->setThreads($threads);
        $this->setTheadStartDelay($threadStartDelay);
        $this->setPollInterval($pollInterval);
    }

    /**
     * @return int
     */
    public function getThreads(): int
    {
        return $this->threads;
    }

    /**
     * @return int
     */
    public function getThreadStartDelay(): int
    {
        return $this->threadStartDelay;
    }

    /**
     * @return int
     */
    public function getPollInterval(): int
    {
        return $this->pollInterval;
    }

    private function setThreads(int $value)
    {
        if ($value < 0 || $value > 20) {
            $value = 20;
        }
        $this->threads = $value;
    }

    private function setTheadStartDelay(int $value)
    {
        if ($value < 0) {
            $value = 0;
        }
        $this->threadStartDelay = $value;
    }

    private function setPollInterval(int $value)
    {
        if ($value < 0) {
            $value = 0;
        }
        $this->pollInterval = $value;
    }
}
