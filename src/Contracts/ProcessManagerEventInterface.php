<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contracts;

use FGhazaleh\MultiProcessManager\Exception\InvalidEventArgumentException;
use FGhazaleh\MultiProcessManager\Exception\InvalidListenerArgumentException;

interface ProcessManagerEventInterface
{
    /**
     * @param string $event
     * @param callable|ListenerInterface $listener
     *@throws InvalidEventArgumentException
     * @throws InvalidListenerArgumentException
     * @return void
     */
    public function listen(string $event, $listener):void ;
}
