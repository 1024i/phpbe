<?php
require __DIR__ . '/vendor/autoload.php';

$runtime = \Phpbe\System\Be::getRuntime();
$runtime->setEnv('local');  // 本地环境
$runtime->setRootPath(__DIR__);
$runtime->execute();

