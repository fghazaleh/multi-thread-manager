<?php
/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

namespace FGhazaleh\MultiThreadManager;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ThreadTest extends TestCase
{
    #[Test]
    public function testMe(): void
    {
        $this->assertTrue(true);
    }
}
