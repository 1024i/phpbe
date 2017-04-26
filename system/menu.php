<?php
namespace system;

/**
 * 菜单基类
 */
abstract class menu
{
	protected $menus = array();
	protected $menu_tree = null;

	public function __construct()
	{
		
	}

	/**
	 * 添加菜单项
	 *
	 * @param int $menu_id 菜单编号
	 * @param int $parent_id 父级菜单编号， 等于0时为顶级菜单
	 * @param string $name 名称
	 * @param string $url 网址
	 * @param string $target 打开方式
	 * @param array $params 参数
	 */
	public function add_menu($menu_id, $parent_id, $name, $url, $target='_self', $params=array(), $home=0)
	{
		$menu = new \stdClass();
		$menu->id = $menu_id;
		$menu->parent_id = $parent_id;
		$menu->name = $name;
		$menu->url = $url;
		$menu->target = $target;
		$menu->params = $params;
		$menu->home = $home;

		$this->menus[$menu_id] = $menu;
	}

	/**
	 * 获取一项菜单 或 整个菜单
	 *
	 * @param int $menu_id 菜单编号
	 * @return object | false
	 */
	public function get_menu($menu_id=0)
	{
		if ($menu_id) {
			if (array_key_exists($menu_id, $this->menus)) {
				return $this->menus[$menu_id];
			} else {
				return false;
			}
		}
		return $this->menus;
	}
	
	/**
	 * 获取菜单树
	 *
	 * @return array()
	 */
	public function get_menu_tree()
	{
		if (!is_array($this->menu_tree)) {
			$this->menu_tree = $this->create_menu_tree();
		}
		return $this->menu_tree;
	}

	/**
	 * 获取当前位置
	 *
	 * @param int $menu_id
	 * @return array
	 */
	public function get_pathway($menu_id=0)
	{
		$pathway = array();
		if (array_key_exists($menu_id, $this->menus)) {
			$pathway[] = $this->menus[$menu_id];
			$parent_id = $this->menus[$menu_id]->parent_id;
			while($parent_id)
			{
				if (array_key_exists($parent_id, $this->menus)) {
					$pathway[] = $this->menus[$parent_id];
					$parent_id = $this->menus[$parent_id]->parent_id;
				} else {
					$parent_id = 0;
				}
			}
		}
		$pathway = array_reverse($pathway, true);
		return $pathway;
	}

    /**
     * 创建菜单树
     * @param int $menu_id
     * @return array | false
     */
	protected function create_menu_tree($menu_id=0)
	{
		$sub_menus = array();
		foreach ($this->menus as $menu) {
			if ($menu->parent_id == $menu_id) {
				$menu->sub_menu = $this->create_menu_tree($menu->id);
				$sub_menus[] = $menu;
			}
		}
		if (count($sub_menus))
			return $sub_menus;
		return false;
	}

	

}