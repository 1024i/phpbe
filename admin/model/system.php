<?php
namespace admin\model;

use \system\be;
use \system\db;

class system extends \system\model
{



	public function watermark($image)
	{
		$lib_image = be::get_lib('image');
		$lib_image->open($image);

		if (!$lib_image->is_image()) {
			$this->set_error('不是合法的图片！');
			return false;
		}

		$width = $lib_image->get_width();
		$height = $lib_image->get_height();

		$config_system_watermark = be::get_config('system_watermark');

		$x = 0;
		$y = 0;
		switch($config_system_watermark->position)
		{
			case 'north':
				$x = $width/2 + $config_system_watermark->offset_x;
				$y = $config_system_watermark->offset_y;
				break;
			case 'northeast':
				$x = $width + $config_system_watermark->offset_x;
				$y = $config_system_watermark->offset_y;
				break;
			case 'east':
				$x = $width + $config_system_watermark->offset_x;
				$y = $height/2 + $config_system_watermark->offset_y;
				break;
			case 'southeast':
				$x = $width + $config_system_watermark->offset_x;
				$y = $height + $config_system_watermark->offset_y;
				break;
			case 'south':
				$x = $width/2 + $config_system_watermark->offset_x;
				$y = $height + $config_system_watermark->offset_y;
				break;
			case 'southwest':
				$x = $config_system_watermark->offset_x;
				$y = $height + $config_system_watermark->offset_y;
				break;
			case 'west':
				$x = $config_system_watermark->offset_x;
				$y = $height/2 + $config_system_watermark->offset_y;
				break;
			case 'northwest':
				$x = $config_system_watermark->offset_x;
				$y = $config_system_watermark->offset_y;
				break;
			case 'center':
				$x = $width/2 + $config_system_watermark->offset_x;
				$y = $height/2 + $config_system_watermark->offset_y;
				break;
		}

		$x = intval($x);
		$y = intval($y);

		if ($config_system_watermark->type == 'text') {
			$style = array();
			$style['font_size'] = $config_system_watermark->text_size;
			$style['color'] = $config_system_watermark->text_color;

			// 添加文字水印
			$lib_image->text($config_system_watermark->text, $x, $y, 0, $style);
		} else {
			// 添加图像水印
			$lib_image->watermark(PATH_DATA.DS.'system'.DS.'watermark'.DS.$config_system_watermark->image, $x, $y);
		}

		$lib_image->save($image);

		return true;
	}

	public function new_log($log)
	{
		$my = be::get_admin_user();
		$row_system_log = be::get_row('system_log');
		$row_system_log->user_id = $my->id;
		$row_system_log->title = $log;
		$row_system_log->ip = $_SERVER['REMOTE_ADDR'];
		$row_system_log->create_time = time();
		$row_system_log->save();
	}

    public function get_logs($option = array())
    {
		$sql = 'SELECT * FROM `be_system_log` WHERE 1'.$this->create_log_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = '`create_time`';
			$order_by_dir = 'DESC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        $logs = db::get_objects($sql, $offset, $limit);
        return $logs;
    }

    
    public function get_log_count($option = array())
    {
        $query = 'SELECT COUNT(*) FROM `be_system_log` WHERE 1 ' . $this->create_log_sql($option);
        return db::get_result($query);
    }

	private function create_log_sql($option = array())
	{
		$sql = '';

		if (array_key_exists('user_id', $option) && $option['user_id']) $sql .= ' AND `user_id`='.$option['user_id'];
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND `title` LIKE \'%' . $option['key'] . '%\'';

		return $sql;
	}

	// 删除三个月(90天)前的后台用户登陆日志
	public function delete_logs()
	{
		return db::execute('DELETE FROM `be_system_log` WHERE `create_time`<'.(time()-90*86400));
	}



    public function get_admins($option = array())
    {
        $query = 'SELECT * FROM `be_user` WHERE `admin_group_id`>0';
        
        return db::get_objects($query);
    }

	// 检查用户是否有权限进行 $action  操作
	public function permission($user, $action)
	{
		// 超级管理员拥有所有权限
		if ($user->admin_group_id == 1) return true;

		$admin_config_user_group = be::get_admin_config('user_group');
		$permissions_field_name = 'permissions_'.$user->admin_group_id;
		$permissions = $admin_config_user_group->$permissions_field_name;

		if (is_array($permissions)) {
			return in_array($action, $permissions);
		} else {
			// 1: 所有权限(如超级管理员) 0或其它值:没有任何权限
			return $permissions == '1';
		}
	}

}
?>