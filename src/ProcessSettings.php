<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager;

use FGhazaleh\MultiProcessManager\Contracts\ProcessSettingsInterface;

final class ProcessSettings implements ProcessSettingsInterface
{
    /**
     * @var int
     */
    private $threads;
    /**
     * @var int
     */
    private $processStartDelay;
    /**
     * @var int
     */
    private $pollInterval;

    public function __construct(int $threads, int $processStartDelay, int $pollInterval)
    {
        $this->setThreads($threads);
        $this->setProcessStartDelay($processStartDelay);
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
    public function getProcessStartDelay(): int
    {
        return $this->processStartDelay;
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

    private function setProcessStartDelay(int $value)
    {
        if ($value < 0) {
            $value = 0;
        }
        $this->processStartDelay = $value;
    }

    private function setPollInterval(int $value)
    {
        if ($value < 0) {
            $value = 0;
        }
        $this->pollInterval = $value;
    }
}
