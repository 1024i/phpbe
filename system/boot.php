<?php
use system\be;
use system\request;
use system\response;

require PATH_ROOT . DS . 'system' . DS . 'loader.php';
spl_autoload_register(array('\\system\\loader', 'autoload'));

require PATH_ROOT . DS . 'system' . DS . 'tool.php';

// 检查网站配置， 是否暂停服务
$config_system = be::get_config('system.system');
if ($config_system->offline === '1') response::end($config_system->offline_message);

// 默认时区
date_default_timezone_set($config_system->timezone);

try {

    // 启动 session
    \system\session::start();

    $my = be::get_user();
    if (!isset($my->id) || $my->id == 0) {
        $model = be::get_service('system.user');
        $model->remember_me();
    }

    //print_r($_SERVER);

    $uri = $_SERVER['REQUEST_URI'];    // 返回值为: /{controller}/{task}......
    $script_name = $_SERVER['SCRIPT_NAME'];
    if ($script_name != '/index.php') $uri = substr($uri, strrpos($script_name, '/index.php'));

    if ($config_system->sef) {
        if ($_SERVER['QUERY_STRING'] != '') $uri = substr($uri, 0, strrpos($uri, '?'));

        $len_sef_suffix = strlen($config_system->sef_suffix);
        if (substr($uri, -$len_sef_suffix, $len_sef_suffix) == $config_system->sef_suffix) {
            $uri = substr($uri, 0, strrpos($uri, $config_system->sef_suffix));
        }

        if (substr($uri, -1, 1) == '/') $uri = substr($uri, 0, -1);

        $uris = explode('/', $uri);
        $len = count($uris);
        if ($len >= 3) {
            $app = $uris[1];
            $controller = $uris[2];
            $_GET['app'] = $_REQUEST['app'] = $app;
            $_GET['controller'] = $_REQUEST['controller'] = $controller;

            if ($len > 2) {
                $router = be::get_router($app, $controller);
                $router->decode_url($uris);
            }
        }
    }

    $app = request::request('app', '');
    $controller = request::request('controller', '');
    $task = request::request('task', '');

    // 默认首页时
    if ($app == '') {
        $home_params = $config_system->home_params;
        foreach ($home_params as $key => $val) {
            $_GET[$key] = $_REQUEST[$key] = $val;
            if ($key == 'app') $app = $val;
            if ($key == 'controller') $controller = $val;
            if ($key == 'task') $task = $val;
        }
    }

    $instance = be::get_controller($app, $controller);
    if ($task == '') $task = 'index';
    if (method_exists($instance, $task)) {

        // 检查用户权限
        $role = be::get_user_role($my->role_id);
        if (!$role->has_permission($app, $controller, $task)) {
            response::error('您没有权限操作该功能！', null, -1024);
        }

        $instance->$task();

    } else {
        response::error('未定义的任务：' . $task, null, -404);
    }
} catch (\exception $e) { // 兼容 < php 7 版本
    \system\error_log::log($e);
    $db = be::get_db();
    if ($db->in_transaction()) $db->rollback();

    $redirect_url = URL_ROOT . '/theme/' . $config_system->theme . '/404.html';
    if ($config_system->debug) {
        response::error('系统错误：' . $e->getMessage(), $redirect_url, -500);
    } else {
        response::error('系统错误！', $redirect_url, -500);
    }
} catch (\throwable $e) {
    \system\error_log::log($e);
    $db = be::get_db();
    if ($db->in_transaction()) $db->rollback();

    $redirect_url = URL_ROOT . '/theme/' . $config_system->theme . '/404.html';
    if ($config_system->debug) {
        response::error('系统错误：' . $e->getMessage(), $redirect_url, -500);
    } else {
        response::error('系统错误！', $redirect_url, -500);
    }
}
