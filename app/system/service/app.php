<?php
namespace app\system\service;

use system\be;

class app extends \system\service
{

    private $be_api = 'http://api.phpbe.com/';

    private $app_tables = null;    
	private $apps = null;

    public function get_apps()
    {
		if ($this->apps == null) {
			$apps = array();

			$config_admin = be::get_config('system.admin');
			if (count($config_admin->apps)) {
				foreach ($config_admin->apps as $app) {
					$apps[] = be::get_app($app);
				}
			}

			$this->apps = $apps;
		}

        return $this->apps;
    }

	public function get_app_count()
    {
		$config_admin = be::get_config('system.admin');
		return count($config_admin->apps);
    }
    
    public function get_remote_apps($option = array())
    {
        $lib_http = be::get_lib('http');
        $response = $lib_http->post($this->be_api . 'apps/', $option);
        
        $apps = json_decode($response);

        return $apps;
    }
        
    public function get_remote_app($app_id)
    {
        $lib_http = be::get_lib('http');
        $response = $lib_http->get($this->be_api . 'app/' . $app_id);
        
        $app = json_decode($response);

		return $app;
    }
    
    
    // 安装应用文件
    public function install($app)
    {
        $lib_http = be::get_lib('http');
        $response = $lib_http->get($this->be_api . 'app_download/'.$app->version->id.'/');

		$zip = PATH_DATA.DS.'system'.DS.'tmp'.DS.'app_'.$app->name.'.zip';
        file_put_contents($zip, $response);

		$dir = PATH_DATA.DS.'system'.DS.'tmp'.DS.'app_'.$app->name;
        $lib_zip = be::get_lib('zip');
        $lib_zip->open($zip);
        if (!$lib_zip->extract_to($dir)) {
            $this->set_error($lib_zip->get_error());
            return false;
        }

		include PATH_ADMIN.DS.'system'.DS.'app.php';
		include $dir.DS.'admin'.DS.'apps'.DS.$app->name.'.php';
		
		$app_class = 'app_'.$app->name;
		$app_obj = new $app_class();
		$app_obj->set_name($app->name);
		$app_obj->install();

		$admin_config_system = be::get_config('system.admin');
        $service_system = be::get_service('system');
		if (!in_array($app->name, $admin_config_system->apps)) {
			$admin_config_system->apps[] = $app->name;
            $service_system->update_config($admin_config_system, PATH_DATA.DS.'admin_config'.DS.'system.php');
		}

		// 删除临时文件
		unlink($zip);

		$lib_fso = be::get_lib('fso');
		$lib_fso->rm_dir($dir);

		return true;
    }
    

    // 删除应用
    public function uninstall($name)
    {
		$admin_config_system = be::get_config('system.admin');

		$apps = array();
		foreach ($admin_config_system->apps as $app) {
			if ($app!=$name) {
				$apps[] = $app;
			}
		}

		$admin_config_system->apps = $apps;
        be::get_service('system')->update_config($admin_config_system, PATH_DATA.DS.'admin_config'.DS.'system.php');

		$app = be::get_app($name);
		$app->uninstall();

        return true;
    }

}
