<?php
namespace system\app;

use \system\be;
use system\request;

/**
 * 应用配轩类
 */
abstract class config
{

    public $app = ''; // 应用名


    public function __construct()
    {
        // \app\user\config
        $this->app = substr(__CLASS__, 5, -7);
    }

    /**
     * 安装应用的配置项
     */
    public function install() {
        $config_items = $this->get_config_items();
        foreach ($config_items as $config_item) {
            $row = be::get_row('config');
            $row->app = $this->app;
            $row->name = $config_item->name;
            $row->key = $config_item->key;
            $row->value = $config_item->value;
            $row->value_type = $config_item->value_type;
            $row->option_type = $config_item->option_type;
            $row->option_values = json_encode($config_item->option_values);
            $row->save();
        }
    }

    /**
     * 删除应用的配置项
     */
    public function uninstall() {

    }


    public function get_config_items() {
        return array();
    }

    /**
     * 配置时保存
     */
    public function save() {
        $config_items = $this->get_config_items();
        foreach ($config_items as $config_item) {
            $value = null;
            switch ($config_item->option_type) {
                case 'file':
                    $value = $_FILES[$config_item->key];
                    break;
                default:
                    if ($config_item->value_type == 'array') {
                        $value = request::post($config_item->key, null);
                    } else {
                        $value = request::post($config_item->key, null, $config_item->value_type);
                    }
            }

            $config_item->save($value);
        }

        $service = be::get_service('system');
        $service->update_cache_config($this->app);
    }

}
