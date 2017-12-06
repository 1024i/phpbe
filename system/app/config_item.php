<?php
namespace system\app;


/**
 * 应用配置项
 */
abstract class config_item
{

    public $name = ''; // 配置项名称
    public $key = ''; // 配置项键名
    public $value = ''; // 配置项值
    public $value_type = ''; // 配置项值 类型
	public $option_type = ''; // 当前版本号
    public $option_values = []; // 应用图标

    /**
     * 构造函数
     *
     * @param $name
     * @param $key
     * @param $value
     * @param string $value_type int, float, bool, string, html, '', array
     * @param string $option_type text, number, date, datetime, range, radio, checkbox, file
     * @param array $option_values
     */
    public function __construct($name ='', $key = '', $value = '', $value_type = 'string', $option_type = null, $option_values = array())
    {
        $this->name = $name;
        $this->key = $key;
        $this->value = $value;
        $this->value_type = $value_type;
        $this->option_type = $option_type;
        $this->option_values = $option_values;
    }


    /**
     * 是否已安装
     *
     * @return bool
     */
    public function is_installed() {
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
