<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;

class Menu extends \Phpbe\System\AdminController
{


    // 菜单管理
    public function menus()
    {
        $groupId = Request::get('groupId', 0, 'int');

        $adminServiceMenu = Be::getService('System', 'Menu');

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

                $rowSystemMenu = Be::getRow('System', 'menu');
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

        $rowSystemMenuGroup = Be::getRow('System', 'menu_group');
        $rowSystemMenuGroup->load($groupId);

        $serviceSystemCache = Be::getService('System', 'Cache');
        $serviceSystemCache->updateMenu($rowSystemMenuGroup->className);

        systemLog('修改菜单：' . $rowSystemMenuGroup->name);

        Response::setMessage('保存菜单成功！');
        Response::redirect('./?app=System&controller=System&action=menus&groupId=' . $groupId);
    }


    public function ajaxMenuDelete()
    {
        $id = Request::post('id', 0, 'int');
        if (!$id) {
            Response::set('error', 2);
            Response::set('message', '参数(id)缺失！');
        } else {
            $rowSystemMenu = Be::getRow('System', 'menu');
            $rowSystemMenu->load($id);

            $adminServiceMenu = Be::getService('System', 'Menu');
            if ($adminServiceMenu->deleteMenu($id)) {

                $rowSystemMenuGroup = Be::getRow('System', 'menu_group');
                $rowSystemMenuGroup->load($rowSystemMenu->groupId);

                $serviceSystemCache = Be::getService('System', 'Cache');
                $serviceSystemCache->updateMenu($rowSystemMenuGroup->className);

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

        $adminServiceSystem = Be::getService('System', 'Admin');
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
            $rowSystemMenu = Be::getRow('System', 'menu');
            $rowSystemMenu->load($id);

            $adminServiceMenu = Be::getService('System', 'Menu');
            if ($adminServiceMenu->setHomeMenu($id)) {

                $rowSystemMenuGroup = Be::getRow('System', 'menuGroup');
                $rowSystemMenuGroup->load($rowSystemMenu->groupId);

                $serviceSystemCache = Be::getService('System', 'cache');
                $serviceSystemCache->updateMenu($rowSystemMenuGroup->className);

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
        $adminServiceMenu = Be::getService('System', 'Menu');

        Response::setTitle('添加新菜单组');
        Response::set('groups', $adminServiceMenu->getMenuGroups());
        Response::display();
    }


    // 修改菜单组
    public function menuGroupEdit()
    {
        $id = Request::request('id', 0, 'int');

        $rowMenuGroup = Be::getRow('System', 'menu_group');
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
        $rowMenuGroup = Be::getRow('System', 'menu_group');
        $rowMenuGroup->load(array('className' => $className));
        if ($rowMenuGroup->id > 0) {
            Response::setMessage('已存在(' . $className . ')类名！', 'error');
            Response::redirect('./?app=System&controller=System&action=menuGroupEdit&id=' . $id);
        }

        if ($id != 0) $rowMenuGroup->load($id);
        $rowMenuGroup->bind(Request::post());
        if ($rowMenuGroup->save()) {
            systemLog($id == 0 ? ('添加新菜单组：' . $rowMenuGroup->name) : ('修改菜单组：' . $rowMenuGroup->name));
            Response::setMessage($id == 0 ? '添加菜单组成功！' : '修改菜单组成功！');

            Response::redirect('./?app=System&controller=System&action=menuGroups');
        } else {
            Response::setMessage($rowMenuGroup->getError(), 'error');
            Response::redirect('./?app=System&controller=System&action=menuGroupEdit&id=' . $id);
        }
    }


    // 删除菜单组
    public function menuGroupDelete()
    {
        $id = Request::post('id', 0, 'int');

        $rowMenuGroup = Be::getRow('System', 'menu_group');
        $rowMenuGroup->load($id);

        if ($rowMenuGroup->id == 0) {
            Response::setMessage('菜单组不存在！', 'error');
        } else {
            if (in_array($rowMenuGroup->className, array('north', 'south', 'dashboard'))) {
                Response::setMessage('系统菜单不可删除！', 'error');
            } else {
                $adminServiceMenu = Be::getService('System', 'menu');
                if ($adminServiceMenu->deleteMenuGroup($rowMenuGroup->id)) {
                    systemLog('成功删除菜单组！');
                    Response::setMessage('成功删除菜单组！');
                } else {
                    Response::setMessage($adminServiceMenu->getError(), 'error');
                }
            }
        }


        Response::redirect('./?app=System&controller=System&action=menuGroups');

    }



}