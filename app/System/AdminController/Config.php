<?php
namespace App\System\AdminController;

use System\Be;
use System\Request;
use System\Response;

/**
 * 配置中心
 *
 * @package App\System\AdminController
 */
class Config extends \System\AdminController
{

    // 配置中心
    public function dashboard()
    {
        $service = Be::getService('System.Config');
        $configTree = $service->getConfigTree();
        Response::set('configTree', $configTree);

        Response::setTitle('配置中心');
        Response::display();
    }

    // 配置
    public function edit()
    {
        $app = Request::get('app');
        $config = Request::get('config');

        $service = Be::getService('System.Config');
        $config = $service->getConfig($app, $config);

        Response::set('config', $config);

        Response::setTitle('配置中心');
        Response::display();
    }

    // 菜单管理
    public function save()
    {

    }


}