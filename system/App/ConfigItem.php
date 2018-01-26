<?php
namespace System\App;


/**
 * 应用配置项
 */
abstract class ConfigItem
{

    public $name = ''; // 配置项名称
    public $key = ''; // 配置项键名
    public $value = ''; // 配置项值
    public $valueType = ''; // 配置项值 类型
	public $optionType = ''; // 当前版本号
    public $optionValues = []; // 应用图标

    /**
     * 构造函数
     *
     * @param $name
     * @param $key
     * @param $value
     * @param string $valueType int, float, bool, string, array
     * @param string $optionType text, number, date, datetime, range, radio, checkbox, file
     * @param array $optionValues
     */
    public function __construct($name ='', $key = '', $value = '', $valueType = 'string', $optionType = null, $optionValues = array())
    {
        $this->name = $name;
        $this->key = $key;
        $this->value = $value;
        $this->valueType = $valueType;
        $this->optionType = $optionType;
        $this->optionValues = $optionValues;
    }


    /**
     * 是否已安装
     *
     * @return bool
     */
    public function isInstalled() {
        return true;
    }

    /**
     * 单独安装此配置项
     *
     * @return bool
     */
    public function install() {
        return true;
    }

    /**
     * 删除此配置项
     *
     * @return bool
     */
    public function uninstall() {
        return true;
    }


    /**
     * 后台编辑时保存
     *
     * @param $value
     */
    public function save($value) {
        $this->value = $value;
    }
}
