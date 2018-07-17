<?php
use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;

require Be::getRuntime()->getPathRoot() . '/System/Loader.php';
spl_autoload_register(array('\\System\\Loader', 'autoload'));

require Be::getRuntime()->getPathRoot() . '/System/Tool.php';
require PATH_ADMIN . '/System/Tool.php';

$configSystem = Be::getConfig('System', 'System');

// 默认时区
date_default_timezone_set($configSystem->timezone);

try {
    // 启动 session
    \Phpbe\System\Session::start();

    $my = Be::getAdminUser();
    if ($my->id == 0) {
        $adminServiceAdminUser = Be::getService('System', 'AdminUser');
        $adminServiceAdminUser->rememberMe();
    }

    $app = Request::request('app', 'System');
    $controller = Request::request('controller', 'AdminUser');
    $action = Request::request('action', 'login');

    $instance = Be::getAdminController($app, $controller);
    if ($instance === null) {
        header('location: ' . Be::getRuntime()->getUrlRoot() . '/404.html');
        exit;
    }

    if (method_exists($instance, $action)) {

        if ($controller != 'AdminUser' || !in_array($action, ['login', 'ajaxLoginCheck', 'logout'])) {

            if ($my->id == 0) {
                Response::error('登录超时，请重新登录！', adminUrl('./?app=system&adminController=adminUser&action=login'));
            }

            // 检查用户权限
            $role = Be::getAdminUserRole($my->roleId);
            if (!$role->hasPermission($app, $controller, $action)) {
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

        $instance->$action();
    } else {
        \Phpbe\System\Response::end('未定义的任务: ' . $action);
    }

} catch (\throwable $e) {
    \Phpbe\System\Log::log($e);
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
            Response::redirect(Be::getRuntime()->getUrlRoot() . '/theme/' . $configSystem->theme . '/500.html');
        }
    }
}


