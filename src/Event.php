<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

enum Event: string
{
    case Started = 'event_started';
    case Finished = 'event_finished';
    case Timeout = 'event_timeout';
}
