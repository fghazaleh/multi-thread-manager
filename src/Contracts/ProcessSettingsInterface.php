<?php
declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contacts;

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