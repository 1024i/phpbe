<?php
use System\Be;
use System\Request;
use System\Response;

require PATH_ROOT . '/System/Loader.php';
spl_autoload_register(array('\\System\\Loader', 'autoload'));

require PATH_ROOT . '/System/Tool.php';
require PATH_ADMIN . '/System/Tool.php';

$configSystem = Be::getConfig('System.System');

// 默认时区
date_default_timezone_set($configSystem->timezone);

try {
    // 启动 session
    \system\session::start();

    $my = Be::getAdminUser();
    if ($my->id == 0) {
        $adminServiceAdminUser = Be::getService('System.AdminUser');
        $adminServiceAdminUser->rememberMe();
    }

    $app = Request::request('app', 'System');
    $controller = Request::request('controller', 'AdminUser');
    $task = Request::request('task', 'login');

    $instance = Be::getAdminController($app, $controller);
    if ($instance === null) {
        header('location: ' . URL_ROOT . '/404.html');
        exit;
    }

    if (method_exists($instance, $task)) {

        if ($controller != 'AdminUser' || !in_array($task, ['login', 'ajaxLoginCheck', 'logout'])) {

            if ($my->id == 0) {
                Response::error('登录超时，请重新登录！', adminUrl('./?app=system&adminController=adminUser&task=login'));
            }

            // 检查用户权限
            $role = Be::getAdminUserRole($my->roleId);
            if (!$role->hasPermission($app, $controller, $task)) {
                $permissionText = '您没有权限操作该功能！';
                if (Request::isAjax()) {
                    $Response = new \stdClass();
                    $Response->error = -1024;
                    $Response->message = $permissionText;
                    echo json_encode($Response);
                    exit();
                } else {
                    Response::end($permissionText);
                }
            }
        }

        $instance->$task();
    } else {
        \system\Response::end('未定义的任务: ' . $task);
    }
} catch (\Exception $e) { // 兼容 < php 7 版本
    \system\Log::log($e);
    $db = Be::getDb();
    if ($db->inTransaction()) $db->rollback();

    if (Request::isAjax()) {
        if ($configSystem->debug) {
            Response::error('系统错误：' . $e->getTraceAsString(), null, -500);
        } else {
            Response::error('系统错误！', null, -500);
        }
    } else {
        if ($configSystem->debug) {
            Response::end('系统错误：' . $e->getMessage());
        } else {
            Response::redirect(URL_ROOT . '/theme/' . $configSystem->theme . '/500.html');
        }
    }
} catch (\throwable $e) {
    \system\Log::log($e);
    $db = Be::getDb();
    if ($db->inTransaction()) $db->rollback();

    if (Request::isAjax()) {
        if ($configSystem->debug) {
            Response::error('系统错误：' . $e->getTraceAsString(), null, -500);
        } else {
            Response::error('系统错误！', null, -500);
        }
    } else {
        if ($configSystem->debug) {
            Response::end('系统错误：' . $e->getMessage());
        } else {
            Response::redirect(URL_ROOT . '/theme/' . $configSystem->theme . '/500.html');
        }
    }
}


