<?php
/**
 * Multi Processing Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiProcessManager\Exception;

final class InvalidEventArgumentException extends \Exception
{
    protected $message = 'Invalid Event.';
}
