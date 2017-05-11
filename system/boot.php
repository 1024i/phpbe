<?php

use \system\be;
use \system\request;
use \system\response;

try {

    require PATH_ROOT . DS . 'system' . DS . 'loader.php';
    spl_autoload_register(array('\\system\\loader', 'autoload'));

    require PATH_ROOT . DS . 'system' . DS . 'tool.php';

    // 检查网站配置， 是否暂停服务
    $config_system = be::get_config('system');
    if ($config_system->offline === '1') response::end($config_system->offline_message);

    // 启动 session
    \system\session::start();

    $my = be::get_user();
    if (!isset($my->id) || $my->id == 0) {
        $model = be::get_model('user');
        $model->remember_me();
    }

    //print_r($_SERVER);

    $uri = $_SERVER['REQUEST_URI'];    // 返回值为: /{controller}/{task}......
    $script_name = $_SERVER['SCRIPT_NAME'];
    if ($script_name != '/index.php') $uri = substr($uri, strrpos($script_name, '/index.php'));

    if ($config_system->sef !== '0') {
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
        if (request::is_ajax()) {
            response::set('error', -404);
            response::set('message', '页面不存在！');
            response::set('redirect_url', URL_ROOT . '/404.html');
            response::ajax();
        } else {
            response::redirect(URL_ROOT . '/404.html');
        }
    }

    if ($task == '') $task = 'index';
    if (method_exists($instance, $task)) {
        /*
        $permission_key = $controller.'.'.$task;

        $app_name = $controller;
        $pos = strpos($controller, '_');
        if ($pos! == false) $app_name = substr($controller, 0, $pos);

        $app = be::get_app($app_name);

        // 检查用户权限
        $role = be::get_role($my->role_id);
        $role_permissions = $role->get_permissions();

        if (isset($config_user_group->$permissions_field_name)) {
            $permissions = $config_user_group->$permissions_field_name;
            if (is_array($permissions)) {
                if ($app) {
                    $matched_app_permission = null;
                    $app_permissions = $app->get_permissions();
                    foreach ($app_permissions as $app_permission) {
                        if (in_array($permission_key, $app_permission[1])) {
                            $matched_app_permission = $app_permission;
                            break;
                        }
                    }

                    if ($matched_app_permission === null) {
                        $permission_text = '您没有权限（该功能未加入权限管理系统）！';
                    } else {


                    }


                    if (isset($app_permission_maps[$permission_key])) {
                        $app_permission_key = $app_permission_maps[$permission_key];
                        if ($app_permission_key == '-' || in_array($app_permission_key, $permissions)) {
                            $permission = true;
                        } else {
                            $app_permissions = $app->get_permissions();
                            $permission_text = '您没有权限：' . $app_permissions[$app_permission_key];
                        }
                    }
                } else {
                    $permission_text = '您没有权限（该功能未加入权限管理系统）！';
                }
            } else {
                // 1: 所有权限 0或其它值:没有任何权限
                if ($permissions == '1') $permission = true;
            }
        }


        if (!$permission && $app) {
            $app_permission_maps = $app->get_permission_maps();
            if (isset($app_permission_maps[$permission_key]) && $app_permission_maps[$permission_key] == '-') $permission = true;
        }


        if (!$permission) {
            if ($permission_text == '') $permission_text = '您没有权限！';

            if (request::is_ajax()) {
                $response = new \stdClass();
                $response->error = -1024;
                $response->message = $permission_text;
                echo json_encode($response);
                exit();
            } else {
                be_exit($permission_text);
            }
        }
        */

        $instance->$task();

    } else {
        if (request::is_ajax()) {
            response::set('error', -404);
            response::set('message', '未定义的任务：' . $task);
            response::ajax();
        } else {
            response::end('未定义的任务：' . $task);
        }
    }

} catch (Throwable $e) {
    \system\error_log::log($e);

    if (request::is_ajax()) {
        response::set('error', -500);
        response::set('message', '系统错误：' . $e->getMessage());
        response::ajax();
    } else {
        //response::redirect(URL_ROOT . '/500.html');
        response::end('系统错误：' . $e->getMessage());
    }
}
