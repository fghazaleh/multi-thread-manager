<?php

/**
 * Multi Threading Manager (PHP package)
 *
 * @author Franco Ghazaleh <franco.ghazaleh@gmail.com>
 */

declare(strict_types=1);

use FGhazaleh\MultiThreadManager\Contracts\ThreadInterface;
use FGhazaleh\MultiThreadManager\Event;
use FGhazaleh\MultiThreadManager\ThreadManager;

require __DIR__ . '/../../vendor/autoload.php';

$threadManager = ThreadManager::create(2)->handleSignals();
$threadManager->listen(Event::Started, function (ThreadInterface $thread) {
    echo 'pid=' . $thread->getPid() . PHP_EOL;
    flush();
});

$threadManager->addThread('exec php -r "sleep(60);"');
$threadManager->addThread('exec php -r "sleep(60);"');
$results = $threadManager->wait();

echo 'done interrupted=' . (int) $threadManager->isInterrupted() . ' results=' . count($results) . PHP_EOL;
