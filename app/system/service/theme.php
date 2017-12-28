<?php

namespace app\system\service;

use System\Be;


class theme extends \System\Service
{
    private $beApi = 'http://api.phpbe.com/';

    private $theme = null;

    public function getThemes()
    {
        if ($this->theme === null) {
            $this->theme = array();

            $dir = dir(PATH_ROOT . DS . 'theme');
            while (($file = $dir->read()) !== false) {
                if ($file != '.' && $file != '..' && is_dir(PATH_ROOT . DS . 'theme' . DS . $file)) {
                    if (file_exists(PATH_ROOT . DS . 'theme' . DS . $file . DS . 'config.php')) {
                        include(PATH_ROOT . DS . 'theme' . DS . $file . DS . 'config.php');
                        $className = 'configTheme_' . $file;
                        if (class_exists($className)) {
                            $this->theme[$file] = new $className();
                        }
                    }
                }

            }
            $dir->close();
        }
        return $this->theme;
    }

    public function getThemeCount()
    {
        return count($this->getThemes());
    }

    public function setDefaultTheme($theme)
    {
        $configSystem = Be::getConfig('System.System');
        $configSystem->theme = $theme;

        Be::getService('system')->updateConfig($configSystem, PATH_ROOT . DS . 'Config' . DS . 'system.php');

        return true;
    }


    public function getRemoteThemes($option = array())
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->post($this->beApi . 'theme/', $option);

        $theme = jsonDecode($Response);
        return $theme;
    }

    public function getRemoteTheme($themeId)
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->get($this->beApi . 'theme/' . $themeId);

        $theme = jsonDecode($Response);
        return $theme;
    }


    // 安装应用文件
    public function installTheme($theme)
    {
        $dir = PATH_ROOT . DS . 'theme' . DS . $theme->name;
        if (file_exists($dir)) {
            $this->setError('安装主题所需要的文件夹（/theme/' . $theme->name . '/）已被占用，请删除后重新安装！');
            return false;
        }

        $libHttp = Be::getLib('Http');
        $Response = $libHttp->get($this->beApi . 'themeDownload/' . $theme->id . '/');

        $zip = PATH_ADMIN . DS . 'tmp' . DS . 'theme_' . $theme->name . '.zip';
        file_put_contents($zip, $Response);

        $libZip = Be::getLib('zip');
        $libZip->open($zip);
        if (!$libZip->extractTo($dir)) {
            $this->setError($libZip->getError());
            return false;
        }

        // 删除临时文件
        unlink($zip);

        return true;
    }

    // 删除主题
    public function uninstallTheme($theme)
    {
        $configSystem = Be::getConfig('System.System');

        if ($configSystem->theme == $theme) {
            $this->setError('正在使用的默认主题不能删除');
            return false;
        }

        $themePath = PATH_ROOT . DS . 'theme' . DS . $theme;

        $libFso = Be::getLib('fso');
        $libFso->rmDir($themePath);

        return true;
    }

}
