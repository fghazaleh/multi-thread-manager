<?php

require_once __DIR__.'/vendor/autoload.php';

$p =Symfony\Component\Process\Process::fromShellCommandline('php -r "echo 1234;"');
$p->start();
$p->wait();
echo $p->getOutput();