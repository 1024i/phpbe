<?php
use System\Be;
use System\Request;
use System\Response;
use System\Session;
use System\Log;

require PATH_ROOT . '/System/Loader.php';
spl_autoload_register(array('\\System\\Loader', 'autoload'));

// 检查网站配置， 是否暂停服务
$configSystem = Be::getConfig('System.System');
if ($configSystem->offline === '1') Response::end($configSystem->offlineMessage);

// 默认时区
date_default_timezone_set($configSystem->timezone);

try {

    // 启动 session
    Session::start();

    //printR($_SERVER);

    $uri = $_SERVER['REQUEST_URI'];    // 返回值为: /{controller}/{task}......

    if (substr($uri, 0, strlen(ADMIN)) == ADMIN) {
        define('IS_BACKEND', true); // 是否后台

        $uri = substr($uri, strlen(ADMIN));
    }

    $scriptName = $_SERVER['SCRIPT_NAME'];
    if ($scriptName != '/index.php') $uri = substr($uri, strrpos($scriptName, '/index.php'));

    if ($configSystem->sef) {
        if ($_SERVER['QUERY_STRING'] != '') $uri = substr($uri, 0, strrpos($uri, '?'));

        $lenSefSuffix = strlen($configSystem->sefSuffix);
        if (substr($uri, -$lenSefSuffix, $lenSefSuffix) == $configSystem->sefSuffix) {
            $uri = substr($uri, 0, strrpos($uri, $configSystem->sefSuffix));
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
                $router = Be::getRouter($app, $controller);
                $router->decodeUrl($uris);
            }
        }
    }

    $app = Request::request('app', '');
    $controller = Request::request('controller', '');
    $task = Request::request('task', '');

    // 默认首页时
    if ($app == '') {
        $homeParams = $configSystem->homeParams;
        foreach ($homeParams as $key => $val) {
            $_GET[$key] = $_REQUEST[$key] = $val;
            if ($key == 'app') $app = $val;
            if ($key == 'controller') $controller = $val;
            if ($key == 'task') $task = $val;
        }
    }

    $instance = Be::getController($app, $controller);
    if ($task == '') $task = 'index';
    if (method_exists($instance, $task)) {

        // 检查用户权限
        $role = Be::getUserRole($my->roleId);
        if (!$role->hasPermission($app, $controller, $task)) {
            Response::error('您没有权限操作该功能！', null, -1024);
        }

        $instance->$task();

    } else {
        Response::error('未定义的任务：' . $task, null, -404);
    }
} catch (\Exception $e) { // 兼容 < php 7 版本
    System\Log::log($e);
    $db = Be::getDb();
    if ($db->inTransaction()) $db->rollback();

    $redirectUrl = URL_ROOT . '/Theme/' . $configSystem->theme . '/404.html';
    if ($configSystem->debug) {
        Response::error('系统错误：' . $e->getMessage(), $redirectUrl, -500);
    } else {
        Response::error('系统错误！', $redirectUrl, -500);
    }
} catch (\throwable $e) {
    System\Log::log($e);
    $db = Be::getDb();
    if ($db->inTransaction()) $db->rollback();

    $redirectUrl = URL_ROOT . '/Theme/' . $configSystem->theme . '/404.html';
    if ($configSystem->debug) {
        Response::error('系统错误：' . $e->getMessage(), $redirectUrl, -500);
    } else {
        Response::error('系统错误！', $redirectUrl, -500);
    }
}
