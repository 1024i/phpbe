<?php
namespace admin\model;

use \system\be;
use \system\db;

class user extends \system\model
{

	// 获取指定条件的用户列表
	public function get_users($option = array())
	{
		$sql = 'SELECT * FROM `be_user` WHERE 1'.$this->create_user_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = '`id`';
			$order_by_dir = 'ASC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        return db::get_objects($sql, $offset, $limit);
	}
    
	// 获取指定条件的用户总数
    public function get_user_count($option = array())
    {
        $query = 'SELECT COUNT(*) FROM `be_user` WHERE 1 ' . $this->create_user_sql($option);
        return db::get_result($query);
    }

	// 生成查找用户的 SQL
	private function create_user_sql($option = array())
	{
		$sql = '';
		
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND (`username` LIKE \'%' . $option['key'] . '%\' OR `name` LIKE \'%' . $option['key'] . '%\' OR `email` LIKE \'%' . $option['key'] . '%\')';
		if (array_key_exists('status', $option) && $option['status']!= -1) $sql .= ' AND `block`='.$option['status'];

		if (array_key_exists('group_id', $option) && $option['group_id']>0) $sql .= ' AND `group_id`='.$option['group_id'];
		if (array_key_exists('admin_group_id', $option) && $option['admin_group_id']>0) $sql .= ' AND `admin_group_id`='.$option['admin_group_id'];

		return $sql;
	}


    public function unblock($ids)
    {
        if (!db::execute('UPDATE `be_user` SET `block`=0 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function block($ids)
    {
        if (!db::execute('UPDATE `be_user` SET `block`=1 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function delete($ids)
    {
        $array = explode(',', $ids);
        foreach ($array as $id) {
            $row_user = be::get_row('user');
            $row_user->load($id);
            $this->delete_avatar_file($row_user);
            $row_user->delete();
        }
        return true;
    }

    public function delete_avatar_file($row_user)
    {
		// 删除旧头像
		if ($row_user->avatar_s!='') @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.$row_user->avatar_s);
		if ($row_user->avatar_m!='') @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.$row_user->avatar_m);
		if ($row_user->avatar_l!='') @unlink(PATH_DATA.DS.'user'.DS.'avatar'.DS.$row_user->avatar_l);
    }

    public function init_avatar($user_id)
    {
        $row_user = be::get_row('user');
        $row_user->load($user_id);

		$this->delete_avatar_file($row_user);

        $row_user->avatar_s = '';
        $row_user->avatar_m = '';
        $row_user->avatar_l = '';
        
        if (!$row_user->save()) {
            $this->set_error($row_user->get_error());
            return false;
        }
        
        return true;
    }


    public function is_username_available($username, $user_id=0)
    {
        return db::get_result('SELECT COUNT(*) FROM `be_user` WHERE '.($user_id>0?'`id`!='.$user_id.' AND ':'').'`username`=\'' . $username . '\'') == 0;
    }

    public function is_email_available($email, $user_id=0)
    {
        return db::get_result('SELECT COUNT(*) FROM `be_user` WHERE '.($user_id>0?('`id`!='.$user_id.' AND '):'').'`email`=\'' . $email . '\'') == 0;
    }

	public function get_groups()
    {
        $sql = 'SELECT * FROM `be_user_group` ORDER BY `rank` ASC';
        return db::get_objects($sql);
    }


}
?>