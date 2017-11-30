<?php
use system\be;
use system\request;
use system\response;

require PATH_ROOT . DS . 'system' . DS . 'loader.php';
spl_autoload_register(array('\\system\\loader', 'autoload'));

require PATH_ROOT . DS . 'system' . DS . 'tool.php';

// 检查网站配置， 是否暂停服务
$config_system = be::get_config('system');
if ($config_system->offline === '1') response::end($config_system->offline_message);

// 默认时区
date_default_timezone_set($config_system->timezone);

try {

    // 启动 session
    \system\session::start();

    $my = be::get_user();
    if (!isset($my->id) || $my->id == 0) {
        $model = be::get_service('user');
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
        if ($len >= 2) {
            $controller = $uris[1];
            $_GET['controller'] = $_REQUEST['controller'] = $controller;

            if ($len > 2) {
                $router = be::get_router($controller);
                $router->decode_url($uris);
            }
        }
    }

    $controller = request::request('controller', '');
    $task = request::request('task', '');

    // 默认首页时
    if ($controller == '') {
        $home_params = $config_system->home_params;
        foreach ($home_params as $key => $val) {
            $_GET[$key] = $_REQUEST[$key] = $val;
            if ($key == 'controller') $controller = $val;
            if ($key == 'task') $task = $val;
        }
    }

    $instance = be::get_controller($controller);
    if ($instance === null) {
        $redirect_url = URL_ROOT . '/theme/' . $config_system->theme . '/404.html';
        if (request::is_ajax()) {
            response::error('页面不存在！', $redirect_url, -404);
        } else {
            response::redirect($redirect_url);
        }
    }

    if ($task == '') $task = 'index';
    if (method_exists($instance, $task)) {

        // 检查用户权限
        $role = be::get_user_role($my->role_id);
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

        $instance->$task();

    } else {
        if (request::is_ajax()) {
            response::error('未定义的任务：' . $task, null, -404);
        } else {
            response::end('未定义的任务：' . $task);
        }
    }
} catch (\throwable $e) {
    \system\error_log::log($e);
    $db = be::get_db();
    if ($db->in_transaction()) $db->rollback();

    if (request::is_ajax()) {
        if ($config_system->debug) {
            response::error('系统错误：' . $e->getMessage(), null, -500);
        } else {
            response::error('系统错误！', null, -500);
        }
    } else {
        if ($config_system->debug) {
            response::end('系统错误：' . $e->getMessage());
        } else {
            response::redirect(URL_ROOT . '/theme/' . $config_system->theme . '/500.html');
        }
    }
}
