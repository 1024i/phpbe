<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;

class System extends \Phpbe\System\AdminController
{

    // 登陆后首页
    public function dashboard()
    {
        $my = Be::getAdminUser();

        Response::setTitle('后台首页');

        $rowAdminUser = Be::getRow('System', 'admin_user');
        $rowAdminUser->load($my->id);
        Response::set('adminUser', $rowAdminUser);

        $adminServiceUser = Be::getService('System', 'AdminUser');
        $userCount = $adminServiceUser->getUserCount();
        Response::set('userCount', $userCount);

        $adminServiceSystem = Be::getService('System', 'Admin');
        $adminServiceApp = Be::getService('System', 'App');
        $adminServiceTheme = Be::getService('System', 'Theme');
        Response::set('recentLogs', $adminServiceSystem->getLogs(array('userId' => $my->id, 'offset' => 0, 'limit' => 10)));
        Response::set('appCount', $adminServiceApp->getAppCount());
        Response::set('themeCount', $adminServiceTheme->getThemeCount());

        Response::display();
    }



    public function cache()
    {
        Response::setTitle('缓存管理');
        Response::display();
    }

    public function clearCache()
    {
        $type = Request::request('type');
        $serviceSystemCache = Be::getService('System', 'Cache');
        $serviceSystemCache->clear($type);

        systemLog('删除缓存（' . $type . '）');

        Response::setMessage('删除缓存成功！');
        Response::redirect(adminUrl('app=System&controller=System&action=cache'));
    }

    // 错误日志
    public function errorLogs()
    {
        $year = Request::request('year', date('Y'));
        $month = Request::request('month', date('m'));
        $day = Request::request('day', date('d'));

        $limit = Request::post('limit', -1, 'int');
        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System', 'Admin');
            $limit = $adminConfigSystem->limit;
        }

        Response::setTitle('错误日志列表');

        $adminServiceSystemErrorLog = Be::getService('system', 'ErrorLog');
        $years = $adminServiceSystemErrorLog->getYears();
        Response::set('years', $years);

        if (!$year && count($years)) $year = $years[0];

        if ($year && in_array($year, $years)) {
            Response::set('year', $year);

            $months = $adminServiceSystemErrorLog->getMonths($year);
            Response::set('months', $months);

            if (!$month && count($months)) $month = $months[0];

            if ($month && in_array($month, $months)) {
                Response::set('month', $month);

                $days = $adminServiceSystemErrorLog->getDays($year, $month);
                Response::set('days', $days);

                if (!$day && count($days)) $day = $days[0];

                if ($day && in_array($day, $days)) {
                    Response::set('day', $day);

                    $option = array();
                    $option['year'] = $year;
                    $option['month'] = $month;
                    $option['day'] = $day;

                    $errorCount = $adminServiceSystemErrorLog->getErrorLogCount($option);
                    Response::set('errorLogCount', $errorCount);

                    $pagination = Be::getUi('Pagination');
                    $pagination->setLimit($limit);
                    $pagination->setTotal($errorCount);
                    $pagination->setPage(Request::request('page', 1, 'int'));
                    Response::set('pagination', $pagination);

                    $option['offset'] = $pagination->getOffset();
                    $option['limit'] = $limit;

                    $errorLogs = $adminServiceSystemErrorLog->getErrorLogs($option);
                    Response::set('errorLogs', $errorLogs);
                }
            }
        }

        Response::display();
    }

    public function errorLog()
    {
        $year = Request::request('year');
        $month = Request::request('month');
        $day = Request::request('day');
        $index = Request::request('index', 0, 'int');

        $adminServiceSystemErrorLog = Be::getService('System', 'ErrorLog');
        $errorLog = $adminServiceSystemErrorLog->getErrorLog($year, $month, $day, $index);
        if (!$errorLog) Response::end($adminServiceSystemErrorLog->getError());

        Response::setTitle('错误详情');
        Response::set('errorLog', $errorLog);
        Response::display();
    }


    // 系统日志
    public function logs()
    {
        $userId = Request::post('userId', 0, 'int');
        $key = Request::post('key', '');
        $limit = Request::post('limit', -1, 'int');
        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System', 'admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceSystem = Be::getService('System', 'Admin');
        Response::setTitle('系统日志');

        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($adminServiceSystem->getLogCount(array('userId' => $userId, 'key' => $key)));
        $pagination->setPage(Request::post('page', 1, 'int'));

        Response::set('pagination', $pagination);
        Response::set('userId', $userId);
        Response::set('key', $key);
        Response::set('adminUsers', $adminServiceSystem->getAdminUsers());
        Response::set('logs', $adminServiceSystem->getLogs(array('userId' => $userId, 'key' => $key, 'offset' => $pagination->getOffset(), 'limit' => $limit)));

        Response::display();
    }

    // 后台登陆日志
    public function ajaxDeleteLogs()
    {
        $adminServiceSystem = Be::getService('System', 'Admin');
        $adminServiceSystem->deleteLogs();

        systemLog('删除三个月前系统日志');

        Response::set('error', 0);
        Response::set('message', '删除日志成功！');
        Response::ajax();
    }

    public function historyBack()
    {
        $libHistory = Be::getLib('History');
        $libHistory->back();
    }

}