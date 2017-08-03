<?php
namespace admin\model;

class system_link extends \model
{

    public function get_system_links($option=array())
    {
		$sql = 'SELECT * FROM `be_system_link` WHERE 1'.$this->create_system_link_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = 'rank';
			$order_by_dir = 'ASC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
			if ($order_by!='id') $sql .= ', `id` ASC';
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        return db::get_objects($sql, $offset, $limit);
    }

    
    public function get_system_link_count($option=array())
    {
        $sql = 'SELECT COUNT(*) FROM `be_system_link` WHERE 1'. $this->create_system_link_sql($option);
        return db::get_value($sql);
    }

	private function create_system_link_sql($option=array())
	{
		$sql = '';
		
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND `title` LIKE \'%' . $option['key'] . '%\'';
		if (array_key_exists('status', $option) && $option['status']!=-1) $sql .= ' AND `block`='.$option['status'];

		return $sql;
	}


    public function unblock($ids)
    {
        if (!db::execute('UPDATE `be_system_link` SET `block`=0 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function block($ids)
    {
        if (!db::execute('UPDATE `be_system_link` SET `block`=1 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function delete($ids)
    {
        if (!db::execute('DELETE FROM `be_system_link` WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function update()
    {
        $sql = 'SELECT * FROM `be_system_link` WHERE `block`=0 ORDER BY `rank` DESC';
        $links = db::get_objects($sql);
        
        $config_system_link = be::get_config('system_link');
        $properties = get_object_vars($config_system_link);
        foreach ($properties as $key=>$val) {
            unset($config_system_link->$key);
        }
        
        $i=1;
        foreach ($links as $link) {
            $key = 'link_'.$i;
            $config_system_link->$key = array('name'=>$link->name, 'url'=>$link->url);
            $i++;
        }
        
        $admin_model_system = be::get_admin_service('system');
        $admin_model_system->save_config_file($config_system_link, PATH_ROOT.DS.'configs'.DS.'system_link.php');
    }

}
?>