<?php
namespace system;

/**
 * 应用基类， 所有应用都从本类继承
 */
abstract class App
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
		$this->installFile();
		$this->installDb();
        $this->installConfig();
	}
	public function installFile()
	{	
	}
	public function installDb()
	{	
	}

	// 查看应用是否已安装
	public function isInstalled()
	{
		return $this->isDbCreated();
	}

	public function getDbTables()
	{
		return array();
	}

	public function getDbInfo($rows = null)
	{
        $info = new \stdClass();
        $info->total = 0;
        $info->created = 0;
        $info->tables = array();

		if ($rows == null) $rows = $this->getDbTables();

		if (!is_array($rows)) return $info;

		$info->total = count($rows);

        $db = Be::getDb();
		$systemTables = $db->getValues('SHOW TABLES');

		$created = 0;
		foreach ($rows as $row) {
			if (in_array($row, $systemTables)) {
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
	public function isDbCreated($rows = null)
	{
		if ($rows == null) $rows = $this->getDbTables();

		if (!is_array($rows)) return false;

        $db = Be::getDb();
		$systemTables = $db->getValues('SHOW TABLES');

		$created = 0;
		foreach ($rows as $row) {
			if (in_array($row, $systemTables)) $created++;
		}

		return count($rows) == $created;
	}


	// 删除时需要执行的操作，如删除数据库表
	public function uninstall()
	{
		$this->uninstallFile();
		$this->uninstallDb();
        $this->uninstallConfig();
	}

	public function uninstallFile()
	{
	}

	public function uninstallDb()
	{
	}

    public function installConfig()
    {
    }

    public function uninstallConfig()
    {
        $db = Be::getDb();
        $sql = 'DELECT * FROM system_config WHERE `app`=\''.$this->name.'\'';
        $db->execute($sql);
    }

	protected function copyDir($src, $dst)
	{
		$my = Be::getUser();

		$src = PATH_ADMIN . DS . 'tmp' . DS . 'app_' . $this->name . DS . $src;

		if (!file_exists($src)) {
			echo '源文件夹'.$src.'不存在';
			// 源文件夹不存在
			return false;
		}

		$libFso = Be::getLib('fso');
		$libFso->copyDir($src, $dst);

		// 安装成功
		return true;
	}

	protected function copyFile($src, $dst)
	{
		$my = Be::getUser();

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

	protected function deleteDir($dir)
	{
		$libFso = Be::getLib('fso');
		$libFso->rmDir($dir);
		return true;
	}

	protected function deleteFile($file)
	{
		unlink($file);
		return true;
	}

    /**
     * 添加配置项
     *
     * @param $name
     * @param $key
     * @param $value
     * @param string $valueType int, float, string, bool, array
     * @param string $optionType text, number, date, datetime, range, radio, checkbox, file
     * @param array $optionValues
     */
    protected function addConfig($name, $key, $value, $valueType = 'string', $optionType = null, $optionValues = array()) {
        if ($optionType == null) {
            switch ($valueType) {
                case 'int':
                case 'float':
                    $optionType = 'number';
                    break;
                case 'string':
                    $optionType = 'text';
                    break;
            }
        }

        $row = Be::getRow('config');
        $row->app = $this->name;
        $row->name = $name;
        $row->key = $key;
        $row->value = $value;
        $row->valueType = $valueType;
        $row->optionType = $optionType;
        $row->optionValues = json_encode($optionValues);
        $row->save();
    }

}
