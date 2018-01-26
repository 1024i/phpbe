<?php
namespace System\App;

use System\Be;
use System\Request;

/**
 * 应用配轩类
 */
abstract class Config
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
        $configItems = $this->getConfigItems();
        foreach ($configItems as $configItem) {
            $row = Be::getRow('config');
            $row->app = $this->app;
            $row->name = $configItem->name;
            $row->key = $configItem->key;
            $row->value = $configItem->value;
            $row->valueType = $configItem->valueType;
            $row->optionType = $configItem->optionType;
            $row->optionValues = json_encode($configItem->optionValues);
            $row->save();
        }
    }

    /**
     * 删除应用的配置项
     */
    public function uninstall() {

    }


    public function getConfigItems() {
        return array();
    }

    /**
     * 配置时保存
     */
    public function save() {
        $configItems = $this->getConfigItems();
        foreach ($configItems as $configItem) {
            $value = null;
            switch ($configItem->optionType) {
                case 'file':
                    $value = $_FILES[$configItem->key];
                    break;
                default:
                    if ($configItem->valueType == 'array') {
                        $value = Request::post($configItem->key, null);
                    } else {
                        $value = Request::post($configItem->key, null, $configItem->valueType);
                    }
            }

            $configItem->save($value);
        }

        $service = Be::getService('system');
        $service->updateCacheConfig($this->app);
    }

}
