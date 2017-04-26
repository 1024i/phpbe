<?php
namespace admin\model;

class system_html extends \model
{

    public function get_system_htmls($option=array())
    {
		$sql = 'SELECT * FROM `be_system_html` WHERE 1'.$this->create_system_html_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = 'id';
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

    
    public function get_system_html_count($option=array())
    {
        $sql = 'SELECT COUNT(*) FROM `be_system_html` WHERE 1'. $this->create_system_html_sql($option);
        return db::get_result($sql);
    }

	private function create_system_html_sql($option=array())
	{
		$sql = '';
		
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND `title` LIKE \'%' . $option['key'] . '%\'';
		if (array_key_exists('status', $option) && $option['status']!=-1) $sql .= ' AND `block`='.$option['status'];

		return $sql;
	}
    
    public function is_class_available($class, $id)
    {
        $sql = 'SELECT COUNT(*) FROM `be_system_html` WHERE `class`=\''.$class.'\' AND `id`!='.$id;
        $n = db::get_result($sql);
        return $n == 0;
    }

    public function unblock($ids)
    {
        $htmls = db::get_objects('SELECT * FROM `be_system_html` WHERE `id` IN(' . $ids . ')');
        foreach ($htmls as $html) {
            $dir = PATH_DATA.DS.'system'.DS.'html';
            if (!file_exists($dir)) {
                $lib_fso = be::get_lib('fso');
                $lib_fso->mk_dir($dir);
            }
			file_put_contents($dir.DS.$html->class.'.html', $html->body);
		}
        
        if (!db::execute('UPDATE `be_system_html` SET `block`=0 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function block($ids)
    {
        $classes = db::get_results('SELECT `class` FROM `be_system_html` WHERE `id` IN(' . $ids . ')');
		foreach ($classes as $class) {
			$path = PATH_DATA.DS.'system'.DS.'html'.DS.$class.'.html';
			if (file_exists($path)) @unlink($path);
		}
        
        if (!db::execute('UPDATE `be_system_html` SET `block`=1 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function delete($ids)
    {
        $classes = db::get_results('SELECT `class` FROM `be_system_html` WHERE `id` IN(' . $ids . ')');
		foreach ($classes as $class) {
			$path = PATH_DATA.DS.'system'.DS.'html'.DS.$class.'.html';
			if (file_exists($path)) @unlink($path);
		}
		
        if (!db::execute('DELETE FROM `be_system_html` WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
		
        return true;
    }


}
?>