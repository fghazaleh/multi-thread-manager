<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Exception;

final class InvalidListenerArgumentException extends \Exception
{
    protected $message = 'Listener should be instance of ListenerInterface or callable function.';
}
