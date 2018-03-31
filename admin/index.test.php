<?php
define('ENV', 'test'); // 正式环境

require 'define.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'define.php';
require PATH_ROOT . '/vendor/autoload.php';
require PATH_ADMIN . '/System/Boot.php';
