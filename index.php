<?php
require __DIR__ . '/vendor/autoload.php';

$runtime = \Be\System\Be::getRuntime();
$runtime->setRootPath(__DIR__);
$runtime->execute();

