<?php

namespace admin\model;

use \system\be;
use \system\db;

class menu extends \system\model
{

    /**
     * 获取菜单项列表
     * @param int $group_id 菜单组编号
     */
    public function get_menus($group_id)
    {
        return db::get_objects('SELECT * FROM `be_system_menu` WHERE `group_id`=' . $group_id . ' ORDER BY `rank` ASC');
    }

    /**
     * 删除菜单
     * @param int $menu_id 菜单编号
     */
    public function delete_menu($menu_id)
    {
        db::execute('UPDATE `be_system_menu` SET `parent_id`=0 WHERE `parent_id`=' . $menu_id);
        db::execute('DELETE FROM `be_system_menu` WHERE `id`=' . $menu_id);
        return true;
    }


    // 将某项菜单设置为首页
    public function set_home_menu($menu_id)
    {
        db::execute('UPDATE `be_system_menu` SET `home`=0 WHERE `home`=1');
        db::execute('UPDATE `be_system_menu` SET `home`=1 WHERE `id`=' . $menu_id);
        return true;
    }


    // 获取菜单组列表
    public function get_menu_groups()
    {
        return db::get_objects('SELECT * FROM `be_system_menu_group` ORDER BY `id` ASC');
    }

    // 获取菜单组中总数
    public function get_menu_group_sum()
    {
        return db::get_result('SELECT COUNT(*) FROM `be_system_menu_group`');
    }


    //删除菜单组
    public function delete_menu_group($group_id)
    {
        db::execute('DELETE FROM `be_system_menu` WHERE `group_id`=' . $group_id);

        $row_system_menu_group = be::get_row('system_menu_group');
        $row_system_menu_group->load($group_id);
        $row_system_menu_group->delete();

        return true;
    }


}
