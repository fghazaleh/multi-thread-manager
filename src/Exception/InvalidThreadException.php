<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager\Exception;
use Exception;
class InvalidThreadException extends Exception
{
    protected $message = 'Invalid Thread.';
}
