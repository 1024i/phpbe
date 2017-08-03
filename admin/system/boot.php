<?php

use system\be;
use system\request;
use system\session;

require PATH_ROOT.DS.'system'.DS.'tool.php';
require PATH_ROOT.DS.'system'.DS.'be.php';

require PATH_ADMIN.DS.'system'.DS.'tool.php';

// 启动 session
session::start();

$my = be::get_admin_user();
if ($my->id == 0) {
    $admin_model_admin_user = be::get_admin_service('admin_user');
    $admin_model_admin_user->remember_me();
}

$controller = request::request('controller', 'admin_user');
$task = request::request('task', 'login');

$instance = be::get_admin_controller($controller);
if ($instance === null) {
	header('location: '.URL_ROOT.'/404.html');
	exit;
}

if (method_exists($instance, $task)) {
    $instance->$task();
} else {
    be_exit('未定义的任务: '.$task);
}
