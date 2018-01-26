<?php
namespace System;

/**
 * 菜单基类
 */
abstract class Menu
{
	protected $menus = array();
	protected $menuTree = null;

	public function __construct()
	{
		
	}

	/**
	 * 添加菜单项
	 *
	 * @param int $menuId 菜单编号
	 * @param int $parentId 父级菜单编号， 等于0时为顶级菜单
	 * @param string $name 名称
	 * @param string $url 网址
	 * @param string $target 打开方式
	 * @param array $params 参数
	 */
	public function addMenu($menuId, $parentId, $name, $url, $target='Self', $params=array(), $home=0)
	{
		$menu = new \stdClass();
		$menu->id = $menuId;
		$menu->parentId = $parentId;
		$menu->name = $name;
		$menu->url = $url;
		$menu->target = $target;
		$menu->params = $params;
		$menu->home = $home;

		$this->menus[$menuId] = $menu;
	}

	/**
	 * 获取一项菜单 或 整个菜单
	 *
	 * @param int $menuId 菜单编号
	 * @return object | false | array
	 */
	public function getMenu($menuId=0)
	{
		if ($menuId) {
			if (array_key_exists($menuId, $this->menus)) {
				return $this->menus[$menuId];
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
	public function getMenuTree()
	{
		if (!is_array($this->menuTree)) {
			$this->menuTree = $this->createMenuTree();
		}
		return $this->menuTree;
	}

	/**
	 * 获取当前位置
	 *
	 * @param int $menuId
	 * @return array
	 */
	public function getPathway($menuId=0)
	{
		$pathway = array();
		if (array_key_exists($menuId, $this->menus)) {
			$pathway[] = $this->menus[$menuId];
			$parentId = $this->menus[$menuId]->parentId;
			while($parentId)
			{
				if (array_key_exists($parentId, $this->menus)) {
					$pathway[] = $this->menus[$parentId];
					$parentId = $this->menus[$parentId]->parentId;
				} else {
					$parentId = 0;
				}
			}
		}
		$pathway = array_reverse($pathway, true);
		return $pathway;
	}

    /**
     * 创建菜单树
     * @param int $menuId
     * @return array | false
     */
	protected function createMenuTree($menuId=0)
	{
		$subMenus = array();
		foreach ($this->menus as $menu) {
			if ($menu->parentId == $menuId) {
				$menu->subMenu = $this->createMenuTree($menu->id);
				$subMenus[] = $menu;
			}
		}
		if (count($subMenus))
			return $subMenus;
		return false;
	}

	

}