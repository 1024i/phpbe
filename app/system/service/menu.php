<?php
namespace App\System\Service;

use System\Be;
use System\Service;

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
     * @return bool
     */
    public function deleteMenu($menuId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.Menu');
            if (!$table->where('parent_id', $menuId)
                ->update(['parent_id' => 0])
            ) {
                throw new \Exception($table->getError());
            }

            $row = Be::getRow('System.Menu');
            if (!$row->delete($menuId)) {
                throw new \Exception($row->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 将某项菜单设置为首页
     *
     * @param $menuId
     * @return bool
     */
    public function setHomeMenu($menuId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.Menu');
            if (!$table->where('home', 1)
                ->update(['home' => 0])
            ) {
                throw new \Exception($table->getError());
            }

            $table = Be::getTable('System.Menu');
            if (!$table->where('id', $menuId)
                ->update(['home' => 1])
            ) {
                throw new \Exception($table->getError());
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
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
     * @return bool
     */
    public function deleteMenuGroup($groupId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.Menu');
            if (!$table->where('group_id', $groupId)
                ->delete()
            ) {
                throw new \Exception($table->getError());
            }

            $row = Be::getRow('System.MenuGroup');
            if (!$row->delete($groupId)) {
                throw new \Exception($row->getError());
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

}
