<?php

namespace admin\controller;

use System\Be;
use System\Request;
use System\Response;

class Theme extends \System\AdminController
{

    // 登陆后首页
    public function dashboard()
    {
        $my = Be::getAdminUser();

        Response::setTitle('后台首页');

        $rowAdminUser = Be::getRow('adminUser');
        $rowAdminUser->load($my->id);
        Response::set('adminUser', $rowAdminUser);

        $adminServiceUser = Be::getService('System.User');
        $userCount = $adminServiceUser->getUserCount();
        Response::set('userCount', $userCount);

        $adminServiceSystem = Be::getService('System.Admin');
        $adminServiceApp = Be::getService('System.app');
        $adminServiceTheme = Be::getService('System.theme');
        Response::set('recentLogs', $adminServiceSystem->getLogs(array('userId' => $my->id, 'offset' => 0, 'limit' => 10)));
        Response::set('appCount', $adminServiceApp->getAppCount());
        Response::set('themeCount', $adminServiceTheme->getThemeCount());

        Response::display();
    }


    // 菜单管理
    public function menus()
    {
        $groupId = Request::get('groupId', 0, 'int');

        $adminServiceMenu = Be::getService('System.menu');

        $groups = $adminServiceMenu->getMenuGroups();
        if ($groupId == 0) $groupId = $groups[0]->id;

        Response::setTitle('菜单列表');
        Response::set('menus', $adminServiceMenu->getMenus($groupId));
        Response::set('groupId', $groupId);
        Response::set('groups', $groups);
        Response::display();
    }

    public function menusSave()
    {
        $groupId = Request::post('groupId', 0, 'int');

        $ids = Request::post('id', array(), 'int');
        $parentIds = Request::post('parentId', array(), 'int');
        $names = Request::post('name', array());
        $urls = Request::post('url', array(), 'html');
        $targets = Request::post('target', array());
        $params = Request::post('params', array());

        if (count($ids) > 0) {
            for ($i = 0, $n = count($ids); $i < $n; $i++) {
                $id = $ids[$i];

                if ($id == 0 && $names[$i] == '') continue;

                $rowSystemMenu = Be::getRow('System.menu');
                if ($id != 0) $rowSystemMenu->load($id);
                $rowSystemMenu->groupId = $groupId;
                $rowSystemMenu->parentId = $parentIds[$i];
                $rowSystemMenu->name = $names[$i];
                $rowSystemMenu->url = $urls[$i];
                $rowSystemMenu->target = $targets[$i];
                $rowSystemMenu->params = $params[$i];
                $rowSystemMenu->ordering = $i;
                $rowSystemMenu->save();
            }
        }

        $rowSystemMenuGroup = Be::getRow('System.menuGroup');
        $rowSystemMenuGroup->load($groupId);

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateCacheMenu($rowSystemMenuGroup->className);

        systemLog('修改菜单：' . $rowSystemMenuGroup->name);

        Response::setMessage('保存菜单成功！');
        Response::redirect('./?app=System&controller=System&task=menus&groupId=' . $groupId);
    }


    public function ajaxMenuDelete()
    {
        $id = Request::post('id', 0, 'int');
        if (!$id) {
            Response::set('error', 2);
            Response::set('message', '参数(id)缺失！');
        } else {
            $rowSystemMenu = Be::getRow('System.menu');
            $rowSystemMenu->load($id);

            $adminServiceMenu = Be::getService('System.menu');
            if ($adminServiceMenu->deleteMenu($id)) {

                $rowSystemMenuGroup = Be::getRow('System.menuGroup');
                $rowSystemMenuGroup->load($rowSystemMenu->groupId);

                $serviceSystem = Be::getService('system');
                $serviceSystem->updateCacheMenu($rowSystemMenuGroup->className);

                Response::set('error', 0);
                Response::set('message', '删除菜单成功！');

                systemLog('删除菜单: #' . $id . ' ' . $rowSystemMenu->name);
            } else {
                Response::set('error', 3);
                Response::set('message', $adminServiceMenu->getError());
            }
        }
        Response::ajax();
    }

    public function menuSetLink()
    {
        $id = Request::get('id', 0, 'int');
        $url = Request::get('url', '', '');

        if ($url != '') $url = base64_decode($url);


        Response::set('url', $url);

        $adminServiceSystem = Be::getService('System.Admin');
        $apps = $adminServiceSystem->getApps();
        Response::set('apps', $apps);

        Response::display();
    }

    public function ajaxMenuSetHome()
    {
        $id = Request::get('id', 0, 'int');
        if ($id == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(id)缺失！');
        } else {
            $rowSystemMenu = Be::getRow('System.menu');
            $rowSystemMenu->load($id);

            $adminServiceMenu = Be::getService('System.menu');
            if ($adminServiceMenu->setHomeMenu($id)) {

                $rowSystemMenuGroup = Be::getRow('System.menuGroup');
                $rowSystemMenuGroup->load($rowSystemMenu->groupId);

                $serviceSystem = Be::getService('system');
                $serviceSystem->updateCacheMenu($rowSystemMenuGroup->className);

                Response::set('error', 0);
                Response::set('message', '设置首页菜单成功！');

                systemLog('设置新首页菜单：#' . $id . ' ' . $rowSystemMenu->name);
            } else {
                Response::set('error', 2);
                Response::set('message', $adminServiceMenu->getError());
            }
        }
        Response::ajax();
    }


    // 菜单分组管理
    public function menuGroups()
    {
        $adminServiceMenu = Be::getService('System.menu');

        Response::setTitle('添加新菜单组');
        Response::set('groups', $adminServiceMenu->getMenuGroups());
        Response::display();
    }


    // 修改菜单组
    public function menuGroupEdit()
    {
        $id = Request::request('id', 0, 'int');

        $rowMenuGroup = Be::getRow('System.menuGroup');
        if ($id != 0) $rowMenuGroup->load($id);

        if ($id != 0)
            Response::setTitle('修改菜单组');
        else
            Response::setTitle('添加新菜单组');

        Response::set('menuGroup', $rowMenuGroup);
        Response::display();
    }

    // 保存修改菜单组
    public function menuGroupEditSave()
    {
        $id = Request::post('id', 0, 'int');

        $className = Request::post('className', '');
        $rowMenuGroup = Be::getRow('System.menuGroup');
        $rowMenuGroup->load(array('className' => $className));
        if ($rowMenuGroup->id > 0) {
            Response::setMessage('已存在(' . $className . ')类名！', 'error');
            Response::redirect('./?app=System&controller=System&task=menuGroupEdit&id=' . $id);
        }

        if ($id != 0) $rowMenuGroup->load($id);
        $rowMenuGroup->bind(Request::post());
        if ($rowMenuGroup->save()) {
            systemLog($id == 0 ? ('添加新菜单组：' . $rowMenuGroup->name) : ('修改菜单组：' . $rowMenuGroup->name));
            Response::setMessage($id == 0 ? '添加菜单组成功！' : '修改菜单组成功！');

            Response::redirect('./?app=System&controller=System&task=menuGroups');
        } else {
            Response::setMessage($rowMenuGroup->getError(), 'error');
            Response::redirect('./?app=System&controller=System&task=menuGroupEdit&id=' . $id);
        }
    }


    // 删除菜单组
    public function menuGroupDelete()
    {
        $id = Request::post('id', 0, 'int');

        $rowMenuGroup = Be::getRow('System.menuGroup');
        $rowMenuGroup->load($id);

        if ($rowMenuGroup->id == 0) {
            Response::setMessage('菜单组不存在！', 'error');
        } else {
            if (in_array($rowMenuGroup->className, array('north', 'south', 'dashboard'))) {
                Response::setMessage('系统菜单不可删除！', 'error');
            } else {
                $adminServiceMenu = Be::getService('System.menu');
                if ($adminServiceMenu->deleteMenuGroup($rowMenuGroup->id)) {
                    systemLog('成功删除菜单组！');
                    Response::setMessage('成功删除菜单组！');
                } else {
                    Response::setMessage($adminServiceMenu->getError(), 'error');
                }
            }
        }


        Response::redirect('./?app=System&controller=System&task=menuGroups');

    }


    // 应用管理
    public function apps()
    {
        $adminServiceApp = Be::getService('System.app');
        $apps = $adminServiceApp->getApps();

        Response::setTitle('已安装的应用');
        Response::set('apps', $apps);
        Response::display();
    }

    public function remoteApps()
    {
        $adminServiceApp = Be::getService('System.app');
        $remoteApps = $adminServiceApp->getRemoteApps(Request::post());

        Response::setTitle('安装新应用');
        Response::set('remoteApps', $remoteApps);
        Response::display();
    }

    public function remoteApp()
    {
        $appId = Request::get('appId', 0, 'int');
        if ($appId == 0) Response::end('参数(appId)缺失！');

        $adminServiceSystem = Be::getService('System.Admin');

        $remoteApp = $adminServiceSystem->getRemoteApp($appId);

        Response::setTitle('安装新应用：' . ($remoteApp->status == '0' ? $remoteApp->app->label : ''));
        Response::set('remoteApp', $remoteApp);
        Response::display();
    }

    public function ajaxInstallApp()
    {
        $appId = Request::get('appId', 0, 'int');
        if ($appId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(appId)缺失！');
            Response::ajax();
        }

        $adminServiceSystem = Be::getService('System.Admin');
        $remoteApp = $adminServiceSystem->getRemoteApp($appId);
        if ($remoteApp->status != '0') {
            Response::set('error', 2);
            Response::set('message', $remoteApp->description);
            Response::ajax();
        }

        $app = $remoteApp->app;
        if (file_exists(PATH_ADMIN . '/apps/' .  $app->name . 'php')) {
            Response::set('error', 3);
            Response::set('message', '已存在安装标识为' . $app->name . '的应用');
            Response::ajax();
        }

        if ($adminServiceSystem->installApp($app)) {
            systemLog('安装新应用：' . $app->name);

            Response::set('error', 0);
            Response::set('message', '应用安装成功！');
        } else {
            Response::set('error', 4);
            Response::set('message', $adminServiceSystem->getError());
        }

        Response::ajax();
    }

    public function ajaxUninstallApp()
    {
        $appName = Request::get('appName', '');
        if ($appName == '') {
            Response::set('error', 1);
            Response::set('message', '参数(appName)缺失！');
            Response::ajax();
        }

        $adminServiceSystem = Be::getService('System.Admin');
        if ($adminServiceSystem->uninstallApp($appName)) {
            systemLog('卸载应用：' . $appName);

            Response::set('error', 0);
            Response::set('message', '应用卸载成功！');
        } else {
            Response::set('error', 2);
            Response::set('message', $adminServiceSystem->getError());
        }

        Response::ajax();
    }


    // 主题管理
    public function themes()
    {
        $adminServiceTheme = Be::getService('System.theme');
        $themes = $adminServiceTheme->getThemes(Request::post());

        Response::setTitle('已安装的主题');
        Response::set('themes', $themes);
        Response::display();
    }

    // 设置默认主题
    public function ajaxThemeSetDefault()
    {
        $theme = Request::get('theme', '');
        if ($theme == '') {
            Response::set('error', 1);
            Response::set('message', '参数(theme)缺失！');
        } else {
            $adminServiceTheme = Be::getService('System.theme');
            if ($adminServiceTheme->setDefaultTheme($theme)) {
                systemLog('设置主题（' . $theme . ') 为默认主题！');

                Response::set('error', 0);
                Response::set('message', '设置默认主题成功！');
            } else {
                Response::set('error', 2);
                Response::set('message', $adminServiceTheme->getError());
            }
        }
        Response::ajax();
    }


    // 在线主题
    public function remoteThemes()
    {
        $adminServiceTheme = Be::getService('System.theme');

        $localThemes = $adminServiceTheme->getThemes();
        $remoteThemes = $adminServiceTheme->getRemoteThemes(Request::post());

        Response::setTitle('安装新主题');
        Response::set('localThemes', $localThemes);
        Response::set('remoteThemes', $remoteThemes);
        Response::display();
    }

    // 安装主题
    public function ajaxInstallTheme()
    {
        $themeId = Request::get('themeId', 0, 'int');
        if ($themeId == 0) {
            Response::set('error', 1);
            Response::set('message', '参数(themeId)缺失！');
            Response::ajax();
        }

        $adminServiceSystem = Be::getService('System.Admin');
        $remoteTheme = $adminServiceSystem->getRemoteTheme($themeId);

        if ($remoteTheme->status != '0') {
            Response::set('error', 2);
            Response::set('message', $remoteTheme->description);
            Response::ajax();
        }

        if ($adminServiceSystem->installTheme($remoteTheme->theme)) {
            systemLog('安装新主题：' . $remoteTheme->theme->name);

            Response::set('error', 0);
            Response::set('message', '主题新安装成功！');
            Response::ajax();
        } else {
            Response::set('error', 3);
            Response::set('message', $adminServiceSystem->getError());
            Response::ajax();
        }
    }


    // 删除主题
    public function ajaxUninstallTheme()
    {
        $theme = Request::get('theme', '');
        if ($theme == '') {
            Response::set('error', 1);
            Response::set('message', '参数(theme)缺失！');
            Response::ajax();
        }

        $adminServiceSystem = Be::getService('System.Admin');
        if ($adminServiceSystem->uninstallTheme($theme)) {
            systemLog('卸载主题：' . $theme);

            Response::set('error', 0);
            Response::set('message', '主题卸载成功！');
            Response::ajax();
        } else {
            Response::set('error', 2);
            Response::set('message', $adminServiceSystem->getError());
            Response::ajax();
        }
    }


    // 系统配置
    public function config()
    {
        Response::setTitle('系统基本设置');
        Response::set('config', Be::getConfig('System.System'));
        Response::display();
    }

    public function configSave()
    {
        $config = Be::getConfig('System.System');
        $config->offline = Request::post('offline', 0, 'int');
        $config->offlineMessage = Request::post('offlineMessage', '', 'html');
        $config->siteName = Request::post('siteName', '');
        $config->sef = Request::post('sef', 0, 'int');
        $config->sefSuffix = Request::post('sefSuffix', '');
        $config->homeTitle = Request::post('homeTitle', '');
        $config->homeMetaKeywords = Request::post('homeMetaKeywords', '');
        $config->homeMetaDescription = Request::post('homeMetaDescription', '');

        $allowUploadFileTypes = Request::post('allowUploadFileTypes', '');
        $allowUploadFileTypes = explode(',', $allowUploadFileTypes);
        $allowUploadFileTypes = array_map('trim', $allowUploadFileTypes);
        $config->allowUploadFileTypes = $allowUploadFileTypes;

        $allowUploadImageTypes = Request::post('allowUploadImageTypes', '');
        $allowUploadImageTypes = explode(',', $allowUploadImageTypes);
        $allowUploadImageTypes = array_map('trim', $allowUploadImageTypes);
        $config->allowUploadImageTypes = $allowUploadImageTypes;

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($config, PATH_DATA . '/config/system.php');

        systemLog('改动系统基本设置');

        Response::setMessage('保存成功！');
        Response::redirect('./?app=System&controller=System&task=config');
    }


    // 邮件服务配置
    public function configMail()
    {
        $config = Be::getConfig('mail');

        Response::setTitle('发送邮件设置');
        Response::set('config', $config);
        Response::display();
    }

    public function configMailSave()
    {
        $config = Be::getConfig('mail');

        $config->fromMail = Request::post('fromMail', '');
        $config->fromName = Request::post('fromName', '');
        $config->smtp = Request::post('smtp', 0, 'int');
        $config->smtpHost = Request::post('smtpHost', '');
        $config->smtpPort = Request::post('smtpPort', 0, 'int');
        $config->smtpUser = Request::post('smtpUser', '');
        $config->smtpPass = Request::post('smtpPass', '');
        $config->smtpSecure = Request::post('smtpSecure', '');

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($config, PATH_DATA . '/config/mail.php');

        systemLog('改动发送邮件设置');

        Response::setMessage('保存成功！');
        Response::redirect('./?app=System&controller=System&task=configMail');
    }

    public function configMailTest()
    {
        Response::setTitle('发送邮件测试');
        Response::display();
    }

    public function configMailTestSave()
    {
        $toEmail = Request::post('toEmail', '');
        $subject = Request::post('subject', '');
        $body = Request::post('body', '', 'html');

        $libMail = Be::getLib('mail');
        $libMail->setSubject($subject);
        $libMail->setBody($body);
        $libMail->to($toEmail);

        if ($libMail->send()) {
            systemLog('发送测试邮件到 ' . $toEmail . ' -成功');
            Response::setMessage('发送邮件成功！');
        } else {
            $error = $libMail->getError();

            systemLog('发送测试邮件到 ' . $toEmail . ' -失败：' . $error);
            Response::setMessage('发送邮件失败：' . $error, 'error');
        }

        Response::redirect('./?app=System&controller=System&task=configMailTest&toEmail=' . $toEmail);
    }


    // 水印设置
    public function configWatermark()
    {
        $config = Be::getConfig('System.Watermark');

        Response::setTitle('水印设置');
        Response::set('config', $config);
        Response::display();
    }

    private function isRgbColor($arr)
    {
        if (!is_array($arr)) return false;
        if (count($arr) != 3) return false;
        foreach ($arr as $x) {
            if (!is_numeric($x)) return false;
            $x = intval($x);
            if ($x < 0) return false;
            if ($x > 255) return false;
        }
        return true;
    }

    public function configWatermarkSave()
    {
        $config = Be::getConfig('System.Watermark');

        $config->watermark = Request::post('watermark', 0, 'int');
        $config->type = Request::post('type', '');
        $config->position = Request::post('position', '');
        $config->offsetX = Request::post('offsetX', 0, 'int');
        $config->offsetY = Request::post('offsetY', 0, 'int');

        $config->text = Request::post('text', '');
        $config->textSize = Request::post('textSize', 0, 'int');

        $textColor = Request::post('textColor', '');
        $textColors = explode(',', $textColor);
        $textColors = array_map('trim', $textColors);

        if (!$this->isRgbColor($textColors)) $textColors = array(255, 0, 0);
        $config->textColor = $textColors;

        $image = $_FILES['image'];
        if ($image['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($image['tmpName']);
            if ($libImage->isImage()) {
                $watermarkName = date('YmdHis') . '.' . $libImage->getType();
                $watermarkPath = PATH_DATA . '/system/watermark/' .  $watermarkName;
                if (move_uploaded_file($image['tmpName'], $watermarkPath)) {
                    // @unlink(PATH_DATA.'/system/watermark/'.$config->image);
                    $config->image = $watermarkName;
                }
            }
        }

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($config, PATH_DATA . '/config/watermark.php');

        systemLog('修改水印设置');

        Response::setMessage('保存成功！');
        Response::redirect('./?app=System&controller=System&task=configWatermark');
    }

    public function configWatermarkTest()
    {
        $src = PATH_DATA . '/system/watermark/test-0.jpg';
        $dst = PATH_DATA . '/system/watermark/test-1.jpg';

        if (!file_exists($src)) Response::end(DATA . '/system/watermakr/test-0.jpg 文件不存在');
        if (file_exists($dst)) @unlink($dst);

        copy($src, $dst);

        sleep(1);

        $adminServiceSystem = Be::getService('System.Admin');
        $adminServiceSystem->watermark($dst);

        Response::setTitle('水印预览');
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
        $serviceSystem = Be::getService('system');

        $serviceSystem->clearCache($type);

        systemLog('删除缓存（' . $type . '）');

        Response::setMessage('删除缓存成功！');
        Response::redirect('./?app=System&controller=System&task=cache');
    }

    // 错误日志
    public function errorLogs()
    {
        $year = Request::request('year', date('Y'));
        $month = Request::request('month', date('m'));
        $day = Request::request('day', date('d'));

        $limit = Request::post('limit', -1, 'int');
        if ($limit == -1) {
            $adminConfigSystem = Be::getConfig('System.admin');
            $limit = $adminConfigSystem->limit;
        }

        Response::setTitle('错误日志列表');

        $adminServiceSystemErrorLog = Be::getAdminService('systemErrorLog');
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

        $adminServiceSystemErrorLog = Be::getAdminService('systemErrorLog');
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
            $adminConfigSystem = Be::getConfig('System.admin');
            $limit = $adminConfigSystem->limit;
        }

        $adminServiceSystem = Be::getService('System.Admin');
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
        $adminServiceSystem = Be::getService('System.Admin');
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

