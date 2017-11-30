<?php
namespace system;

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
        
    }

	public function set_name($name)
	{
		$this->name = $name;
	}


    /**
     * 获取所有前台菜单项
     * 
     * @return array
     */
    public function get_menus()
    {
        return array();
    }


    /**
     * 获取所有后台菜单项
     * 
     * @return array
     */
    public function get_admin_menus()
    {
        return array();
    }



    /**
     * 获取所有前台权限项
     * 
     * @return array
     */
    public function get_permissions()
    {
        return array();
    }


    /**
     * 获取所有后台权限项
     * 
     * @return array
     */
    public function get_admin_permissions()
    {
        return array();
    }


	// 安装时需要执行的操作，如创建数据库表
	public function install()
	{
		$this->install_file();
		$this->install_db();
	}
	public function install_file()
	{	
	}
	public function install_db()
	{	
	}

	// 查看应用是否已安装
	public function is_installed()
	{
		return $this->is_db_created();
	}

	public function get_db_tables()
	{
		return array();
	}

	public function get_db_info($rows = null)
	{
        $info = new \stdClass();
        $info->total = 0;
        $info->created = 0;
        $info->tables = array();

		if ($rows == null) $rows = $this->get_db_tables();

		if (!is_array($rows)) return $info;

		$info->total = count($rows);

        $db = be::get_db();
		$system_tables = $db->get_values('SHOW TABLES');

		$created = 0;
		foreach ($rows as $row) {
			if (in_array($row, $system_tables)) {
				$info->tables[$row] = true;
				$created++;
			} else {
				$info->tables[$row] = false;
			}
		}
		$info->created = $created;
		return $info;
	}

	// 判断应用相关的数据库表是否已创建
	public function is_db_created($rows = null)
	{
		if ($rows == null) $rows = $this->get_db_tables();

		if (!is_array($rows)) return false;

        $db = be::get_db();
		$system_tables = $db->get_values('SHOW TABLES');

		$created = 0;
		foreach ($rows as $row) {
			if (in_array($row, $system_tables)) $created++;
		}

		return count($rows) == $created;
	}


	// 删除时需要执行的操作，如删除数据库表
	public function uninstall()
	{
		$this->uninstall_file();
		$this->uninstall_db();
	}

	public function uninstall_file()
	{
	}

	public function uninstall_db()
	{
	}

	protected function copy_dir($src, $dst)
	{
		$my = be::get_user();

		$src = PATH_ADMIN . DS . 'tmp' . DS . 'app_' . $this->name . DS . $src;

		if (!file_exists($src)) {
			echo '源文件夹'.$src.'不存在';
			// 源文件夹不存在
			return false;
		}

		$lib_fso = be::get_lib('fso');
		$lib_fso->copy_dir($src, $dst);

		// 安装成功
		return true;
	}

	protected function copy_file($src, $dst)
	{
		$my = be::get_user();

		$src = PATH_ADMIN . DS . 'tmp' . DS . 'app_' . $this->name . DS . $src;

		if (!file_exists($src)) {
			echo '源文件'.$src.'不存在';
			// 源文件不存在
			return false;
		}

		copy($src, $dst);

		// 安装成功		
		return true;
	}

	protected function delete_dir($dir)
	{
		$lib_fso = be::get_lib('fso');
		$lib_fso->rm_dir($dir);
		return true;
	}

	protected function delete_file($file)
	{
		unlink($file);
		return true;
	}

}
