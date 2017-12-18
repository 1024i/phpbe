<?php
define('ENVIRONMENT', 'test'); // 正式环境

require 'define.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'define.php';
require PATH_ADMIN . DS . 'system' . DS . 'boot.php';
