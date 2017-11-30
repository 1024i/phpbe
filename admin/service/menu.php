<?php

namespace admin\service;

use system\be;

class menu extends \system\service
{

    /**
     * 获取菜单项列表
     *
     * @param int $group_id 菜单组编号
     * @return array
     */
    public function get_menus($group_id)
    {
        return be::get_table('system_menu')
            ->where('group_id', $group_id)
            ->order_by('rank', 'asc')
            ->get_objects();
    }

    /**
     * 删除菜单
     *
     * @param int $menu_id 菜单编号
     * @return bool
     */
    public function delete_menu($menu_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system_menu');
            if (!$table->where('parent_id', $menu_id)
                ->update(['parent_id' => 0])
            ) {
                throw new \exception($table->get_error());
            }

            $row = be::get_row('system_menu');
            if (!$row->delete($menu_id)) {
                throw new \exception($row->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 将某项菜单设置为首页
     *
     * @param $menu_id
     * @return bool
     */
    public function set_home_menu($menu_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system_menu');
            if (!$table->where('home', 1)
                ->update(['home' => 0])
            ) {
                throw new \exception($table->get_error());
            }

            $table = be::get_table('system_menu');
            if (!$table->where('id', $menu_id)
                ->update(['home' => 1])
            ) {
                throw new \exception($table->get_error());
            }
            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 获取菜单组列表
     *
     * @return array
     */
    public function get_menu_groups()
    {
        return be::get_table('system_menu_group')->order_by('id', 'asc')->get_objects();
    }

    /**
     * 获取菜单组中总数
     *
     * @return int
     */
    public function get_menu_group_sum()
    {
        return be::get_table('system_menu_group')->count();
    }

    /**
     * 删除菜单组
     *
     * @param $group_id
     * @return bool
     */
    public function delete_menu_group($group_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system_menu');
            if (!$table->where('group_id', $group_id)
                ->delete()
            ) {
                throw new \exception($table->get_error());
            }

            $row = be::get_row('system_menu_group');
            if (!$row->delete($group_id)) {
                throw new \exception($row->get_error());
            }
            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

}
