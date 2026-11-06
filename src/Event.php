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
    /** The process exited with code 0. */
    case Finished = 'event_finished';
    /** The process exited with a non-zero code. */
    case Failed = 'event_failed';
    /** A failed or timed out thread is scheduled for another attempt. */
    case Retrying = 'event_retrying';
    /** The process exceeded its timeout and was stopped. */
    case Timeout = 'event_timeout';
}
