<?php

use system\be;
use system\request;
use system\response;

require PATH_ROOT . DS . 'system' . DS . 'loader.php';
spl_autoload_register(array('\\system\\loader', 'autoload'));

require PATH_ROOT . DS . 'system' . DS . 'tool.php';
require PATH_ADMIN.DS.'system'.DS.'tool.php';

$config_system = be::get_config('system');

// 默认时区
date_default_timezone_set($config_system->timezone);

try {
    // 启动 session
    \system\session::start();

    $my = be::get_admin_user();
    if ($my->id == 0) {
        $admin_service_admin_user = be::get_admin_service('admin_user');
        $admin_service_admin_user->remember_me();
    }

    $controller = request::request('controller', 'admin_user');
    $task = request::request('task', 'login');

    $instance = be::get_admin_controller($controller);
    if ($instance === null) {
        header('location: '.URL_ROOT.'/404.html');
        exit;
    }

    if (method_exists($instance, $task)) {

        if ($controller != 'admin_user' || !in_array($task, ['login', 'ajax_login_check', 'logout'])) {

            if ($my->id == 0) {
                response::error('登录超时，请重新登录！', './?controller=admin_user&task=login');
            }

            // 检查用户权限
            $role = be::get_admin_user_role($my->role_id);
            if (!$role->has_permission($controller, $task)) {
                $permission_text = '您没有权限操作该功能！';
                if (request::is_ajax()) {
                    $response = new \stdClass();
                    $response->error = -1024;
                    $response->message = $permission_text;
                    echo json_encode($response);
                    exit();
                } else {
                    response::end($permission_text);
                }
            }
        }

        $instance->$task();
    } else {
        \system\response::end('未定义的任务: '.$task);
    }
} catch (\throwable $e) {
    \system\error_log::log($e);
    $db = be::get_db();
    if ($db->in_transaction()) $db->rollback();

    if (request::is_ajax()) {
        if ($config_system->debug) {
            response::error('系统错误：' . $e->getTraceAsString(), null, -500);
        } else {
            response::error('系统错误！', null, -500);
        }
    } else {
        if ($config_system->debug) {
            print_r($e->getTrace());
            response::end('系统错误：' . $e->getMessage());
        } else {
            response::redirect(URL_ROOT . '/theme/' . $config_system->theme . '/500.html');
        }
    }
}


