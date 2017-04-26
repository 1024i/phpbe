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
		return db::get_objects('SELECT * FROM `be_system_menu` WHERE `group_id`='.$group_id.' ORDER BY `rank` ASC');
	}


	/**
	 * 保存菜单为静态 php 文件以提高执行效率
	 * @param int $group_id
	 */
	public function update_menu($group_id)
	{
		$group = be::get_row('system_menu_group');
		$group->load($group_id);

		$menus = $this->get_menus($group_id);

		$s  = '<?php' . "\r\n";
		$s .= 'class menu_'.$group->class_name.' extends menu' . "\r\n";
		$s .= '{' . "\r\n";
		$s .= '  public function __construct()' . "\r\n";
		$s .= '  {' . "\r\n";
		foreach ($menus as $menu) {
			if ($menu->home == 1) {
				$home_params = array();

				$menu_params = $menu->params;
				if ($menu_params == '') $menu_params = $menu->url;

				if (strpos($menu_params, '=')) {
					$menu_params = explode('&', $menu_params);
					foreach ($menu_params as $menu_param) {
						$menu_param = explode('=', $menu_param);
						if (count($menu_param) == 2) $home_params[ $menu_param[0] ] = $menu_param[1];
					}
				}

				$config_system = be::get_config('system');
				$config_system->home_params = $home_params;
				config::save($config_system, PATH_ROOT.DS.'configs'.DS.'system.php');
			}

			$params = array();

			$menu_params = $menu->params;
			if ($menu_params == '') $menu_params = $menu->url;

			if (strpos($menu_params, '=')) {
				$menu_params = explode('&', $menu_params);
				foreach ($menu_params as $menu_param) {
					$menu_param = explode('=', $menu_param);
					if (count($menu_param) == 2) $params[] = '\''.$menu_param[0].'\'=>\''.$menu_param[1].'\'';
				}
			}

			$param = 'array('. implode(',', $params).')';
			
			$url = $menu->url;			
			if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') {
				$url = '\''.$url.'\'';
			} else {
				$url = 'url(\''.$url.'\')';
			}

			$s .= '    $this->add_menu('.$menu->id.', '.$menu->parent_id.', \''.$menu->name.'\', '.$url.', \''.$menu->target.'\', '.$param.', '.$menu->home.');'."\r\n";
		}
		$s .= '  }' . "\r\n";
		$s .= '}' . "\r\n";
		$s .= '?>';

		file_put_contents(PATH_ROOT.DS.'menus'.DS.$group->class_name.'.php', $s);
	}
    

	/**
	 * 删除菜单
	 * @param int $menu_id 菜单编号
	 */
	public function delete_menu($menu_id)
	{
		db::execute('UPDATE `be_system_menu` SET `parent_id`=0 WHERE `parent_id`='.$menu_id);
		db::execute('DELETE FROM `be_system_menu` WHERE `id`='.$menu_id);
		return true;
	}
	
	
	// 将某项菜单设置为首页
	public function set_home_menu($menu_id)
	{
		db::execute('UPDATE `be_system_menu` SET `home`=0 WHERE `home`=1');
		db::execute('UPDATE `be_system_menu` SET `home`=1 WHERE `id`='.$menu_id);
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
		db::execute('DELETE FROM `be_system_menu` WHERE `group_id`='.$group_id);

		$row_system_menu_group = be::get_row('system_menu_group');
		$row_system_menu_group->load($group_id);

		unlink(PATH_ROOT.DS.'menus'.DS.$row_system_menu_group->class_name.'.php');

		$row_system_menu_group->delete();

		return true;
	}



}
?>