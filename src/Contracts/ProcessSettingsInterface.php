<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contracts;

interface ProcessSettingsInterface
{
    /**
     * @return int
     */
    public function getThreads(): int;

    /**
     * @return int
     */
    public function getProcessStartDelay(): int;

    /**
     * @return int
     */
    public function getPollInterval(): int;
}
