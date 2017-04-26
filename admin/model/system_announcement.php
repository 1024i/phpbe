<?php
namespace admin\model;

use \system\be;
use \system\db;

class system_announcement extends \system\model
{

    public function get_system_announcements($option=array())
    {
		$sql = 'SELECT * FROM `be_system_announcement` WHERE 1'.$this->create_system_announcement_where($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = 'rank';
			$order_by_dir = 'DESC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
			if ($order_by!='id') $sql .= ', `id` DESC';
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        return db::get_objects($sql, $offset, $limit);
    }

    
    public function get_system_announcement_count($option=array())
    {
        return be::get_table('system_announcement')
            ->where($this->create_system_announcement_where($option))
            ->count();
    }

	private function create_system_announcement_where($option=array())
	{
		$where = array();
		
		if (array_key_exists('key', $option) && $option['key']) {
            $where[] = '(';
            $where[] = array('title', 'like', '%'.$option['key'].'%');
            $where[] = 'OR';
            $where[] = array('content', 'like', '%'.$option['key'].'%');
            $where[] = ')';
        }

		if (array_key_exists('status', $option) && $option['status']!=-1){
            $where[] = array('block', $option['status']);
        }

		return $where;
	}


    public function unblock($ids)
    {
        $table = be::get_table('system_announcement');
        if (!$table->where('id', 'in', explode(',', $ids))->update(array('block', 0))){
            $this->set_error($table->get_error());
            return false;
        }
        return true;
    }

    public function block($ids)
    {
        $table = be::get_table('system_announcement');
        if (!$table->where('id', 'in', explode(',', $ids))->update(array('block', 1))){
            $this->set_error($table->get_error());
            return false;
        }
        return true;
    }

    public function delete($ids)
    {
        $table = be::get_table('system_announcement');
        if (!$table->where('id', 'in', explode(',', $ids))->delete()){
            $this->set_error($table->get_error());
            return false;
        }
        return true;
    }


}
?>