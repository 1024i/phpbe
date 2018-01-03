<?php
namespace system\app;

use \system\be;

/**
 * 应用基类， 所有应用都从本类继承
 */
abstract class db
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


    public function install()
    {
    }

    // 查看应用是否已安装
    public function isInstalled()
    {
        return $this->isCreated();
    }

    public function getDbTables()
    {
        return array();
    }

    public function getInfo($rows = null)
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
    public function isCreated($rows = null)
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
    }


}
