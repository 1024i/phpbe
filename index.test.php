<?php
require __DIR__ . '/vendor/autoload.php';

$runtime = \Phpbe\System\Be::getRuntime();
$runtime->setEnv('test');  // 测试环境
$runtime->setRootPath(__DIR__);
$runtime->execute();
