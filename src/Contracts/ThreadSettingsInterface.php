<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

interface ThreadSettingsInterface
{
    /**
     * @return int
     */
    public function getThreadSize(): int;

    /**
     * @return int
     */
    public function getThreadStartDelay(): int;

    /**
     * @return int
     */
    public function getPollInterval(): int;
}
