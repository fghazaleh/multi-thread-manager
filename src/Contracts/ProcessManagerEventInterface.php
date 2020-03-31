<?php

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Contracts;

interface ProcessManagerEventInterface
{
    /**
     * @param string $event
     * @param callable|ListenerInterface $listener
     * @throws \InvalidArgumentException
     * @return void
     */
    public function listenOn(string $event, $listener):void ;
}
