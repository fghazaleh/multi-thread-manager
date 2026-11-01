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
    public function getThreadSize(): int;

    public function getThreadStartDelay(): int;

    public function getPollInterval(): int;

    /**
     * Whether the remaining pending and running threads are terminated
     * as soon as one thread fails or times out.
     */
    public function isFailFast(): bool;
}
