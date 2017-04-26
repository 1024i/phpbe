<?php
namespace admin\model;

use \system\be;


class theme extends \system\model
{

	private $themes = null;
	public function get_themes()
	{
		if ($this->themes === null) {
			$this->themes = array();
			
			$dir = dir(PATH_ROOT . DS . 'themes');
			while (($file = $dir->read()) !== false)
			{
				if ($file!='.' && $file!='..' && is_dir(PATH_ROOT.DS.'themes'.DS.$file)) {
					if (file_exists(PATH_ROOT . DS . 'themes' . DS . $file . DS . 'config.php')) {
						include(PATH_ROOT . DS . 'themes' . DS . $file . DS . 'config.php');
						$class_name = 'config_theme_' . $file;
						if (class_exists($class_name)) {
							$this->themes[ $file ] = new $class_name();
						}
					}
				}
					
			}
			$dir->close();
		}
		return $this->themes;
	}

	public function get_theme_count()
	{
		return count($this->get_themes());
	}

	public function set_default_theme($theme)
	{
		$config_system = be::get_config('system');
		$config_system->theme = $theme;

		$this->save_config_file($config_system, PATH_ROOT . DS . 'configs' . DS . 'system.php');

		return true;
	}


    public function get_remote_themes($option = array())
    {
        $lib_http = be::get_lib('http');
        $response = $lib_http->post($this->be_api . 'themes/', $option);

        $themes = json_decode($response);
        return $themes;
    }
        
    public function get_remote_theme($theme_id)
    {
        $lib_http = be::get_lib('http');
        $response = $lib_http->get($this->be_api . 'theme/' . $theme_id);
        
        $theme = json_decode($response);
		return $theme;
    }


    // 安装应用文件
    public function install_theme($theme)
    {
		$dir = PATH_ROOT . DS . 'themes' . DS . $theme->name;
		if (file_exists($dir)) {
            $this->set_error('安装主题所需要的文件夹（/themes/' . $theme->name . '/）已被占用，请删除后重新安装！');
            return false;
		}

        $lib_http = be::get_lib('http');
        $response = $lib_http->get($this->be_api . 'theme_download/' . $theme->id . '/');

		$zip = PATH_ADMIN . DS . 'tmp' . DS . 'theme_' . $theme->name . '.zip';
        file_put_contents($zip, $response);

        $lib_zip = be::get_lib('zip');
        $lib_zip->open($zip);
        if (!$lib_zip->extract_to($dir)) {
            $this->set_error($lib_zip->get_error());
            return false;
        }

		// 删除临时文件
		unlink($zip);

		return true;
    }

    // 删除主题
    public function uninstall_theme($theme)
    {
		$config_system = be::get_config('system');

		if ($config_system->theme == $theme) {
			$this->set_error('正在使用的默认主题不能删除');
			return false;
		}

		$theme_path = PATH_ROOT . DS . 'themes' . DS . $theme;

		$lib_fso = be::get_lib('fso');
		$lib_fso->rm_dir($theme_path);
		
		return true;
    }

}
