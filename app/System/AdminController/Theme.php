<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;

class Theme extends \Phpbe\System\AdminController
{



    // 主题管理
    public function themes()
    {
        $adminServiceTheme = Be::getService('System', 'Theme');
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
            $adminServiceTheme = Be::getService('System', 'Theme');
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
        $adminServiceTheme = Be::getService('System', 'Theme');

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

        $adminServiceSystem = Be::getService('System', 'Admin');
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

        $adminServiceSystem = Be::getService('System', 'Admin');
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


}