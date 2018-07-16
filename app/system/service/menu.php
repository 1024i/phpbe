<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;
use Phpbe\System\Service\ServiceException;

class menu extends Service
{

    /**
     * 获取菜单项列表
     *
     * @param int $groupId 菜单组编号
     * @return array
     */
    public function getMenus($groupId)
    {
        return Be::getTable('System.Menu')
            ->where('group_id', $groupId)
            ->orderBy('ordering', 'ASC')
            ->getObjects();
    }

    /**
     * 删除菜单
     *
     * @param int $menuId 菜单编号
     * @throws \Exception
     */
    public function deleteMenu($menuId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System.Menu')->where('parent_id', $menuId)->update(['parent_id' => 0]);
            Be::getRow('System.Menu')->delete($menuId);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 将某项菜单设置为首页
     *
     * @param $menuId
     * @throws \Exception
     */
    public function setHomeMenu($menuId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System.Menu')->where('home', 1)->update(['home' => 0]);
            Be::getTable('System.Menu')->where('id', $menuId)->update(['home' => 1]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 获取菜单组列表
     *
     * @return array
     */
    public function getMenuGroups()
    {
        return Be::getTable('System.MenuGroup')->orderBy('id', 'asc')->getObjects();
    }

    /**
     * 获取菜单组中总数
     *
     * @return int
     */
    public function getMenuGroupSum()
    {
        return Be::getTable('System.MenuGroup')->count();
    }

    /**
     * 删除菜单组
     *
     * @param $groupId
     * @throws \Exception
     */
    public function deleteMenuGroup($groupId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System.Menu')->where('group_id', $groupId)->delete();
            Be::getRow('System.MenuGroup')->delete($groupId);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

}
