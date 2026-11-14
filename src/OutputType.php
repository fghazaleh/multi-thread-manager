<?php

/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

enum OutputType: string
{
    case Stdout = 'out';
    case Stderr = 'err';
}
