<?php
namespace System\App;

use System\Be;

/**
 * 应用基类， 所有应用都从本类继承
 */
abstract class File
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


    protected function copyDir($src, $dst)
    {
        $src = PATH_ADMIN . '/tmp/app_' . $this->name . '/' . $src;

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
         $src = PATH_ADMIN . '/tmp/app_' . $this->name . '/' . $src;

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


}
