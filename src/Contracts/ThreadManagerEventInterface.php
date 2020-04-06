<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Contracts;

use FGhazaleh\MultiThreadManager\Exception\InvalidEventArgumentException;
use FGhazaleh\MultiThreadManager\Exception\InvalidListenerArgumentException;

interface ThreadManagerEventInterface
{
    /**
     * @param string $event
     * @param callable|ListenerInterface $listener
     * @throws InvalidEventArgumentException
     * @throws InvalidListenerArgumentException
     * @return void
     */
    public function listen(string $event, $listener):void ;
}
