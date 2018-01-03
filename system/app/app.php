<?php
namespace system\app;

/**
 * 应用基类， 所有应用都从本类继承
 */
abstract class app
{
	public $id = 0; // 应用在BE网站上的编号, 以便升级更新
    public $name = ''; // 应用名
	public $label = ''; // 中文标识名， 如 '用户管理系统'
	public $version = '1.0'; // 当前版本号
    public $icon = null; // 应用图标

    /**
     * 构造函数
     * 
	 * @param int $id 该应用在BE网站上的编号
     * @param string $label 应用中文名
     * @param string $version 应用版本号
     * @param string $icon 图标
     * 
     */
    public function __construct($id, $label, $version, $icon)
    {
        $this->id = $id;
		$this->label = $label;
		$this->version = $version;
        $this->icon = $icon;
        $this->name = __CLASS__;
    }

    /**
     * 获取所有前台菜单项
     * 
     * @return array
     */
    public function getMenus()
    {
        return array();
    }


    /**
     * 获取所有后台菜单项
     * 
     * @return array
     */
    public function getAdminMenus()
    {
        return array();
    }



    /**
     * 获取所有前台权限项
     * 
     * @return array
     */
    public function getPermissions()
    {
        return array();
    }


    /**
     * 获取所有后台权限项
     * 
     * @return array
     */
    public function getAdminPermissions()
    {
        return array();
    }


	// 安装时需要执行的操作，如创建数据库表
	public function install()
	{

	}


	// 查看应用是否已安装
	public function isInstalled()
	{
	}

	// 删除时需要执行的操作，如删除数据库表
	public function uninstall()
	{

	}


}
