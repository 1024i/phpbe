<?php
namespace app\system\service;

use System\Be;

class app extends \System\Service
{

    private $beApi = 'http://api.phpbe.com/';

    private $appTables = null;
	private $apps = null;

    public function getApps()
    {
		if ($this->apps == null) {
			$apps = array();

			$configAdmin = Be::getConfig('System.admin');
			if (count($configAdmin->apps)) {
				foreach ($configAdmin->apps as $app) {
					$apps[] = Be::getApp($app);
				}
			}

			$this->apps = $apps;
		}

        return $this->apps;
    }

	public function getAppCount()
    {
		$configAdmin = Be::getConfig('System.admin');
		return count($configAdmin->apps);
    }
    
    public function getRemoteApps($option = array())
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->post($this->beApi . 'apps/', $option);
        
        $apps = jsonDecode($Response);

        return $apps;
    }
        
    public function getRemoteApp($appId)
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->get($this->beApi . 'app/' . $appId);
        
        $app = jsonDecode($Response);

		return $app;
    }
    
    
    // 安装应用文件
    public function install($app)
    {
        $libHttp = Be::getLib('Http');
        $Response = $libHttp->get($this->beApi . 'appDownload/'.$app->version->id.'/');

		$zip = PATH_DATA.DS.'system'.DS.'tmp'.DS.'app_'.$app->name.'.zip';
        file_put_contents($zip, $Response);

		$dir = PATH_DATA.DS.'system'.DS.'tmp'.DS.'app_'.$app->name;
        $libZip = Be::getLib('zip');
        $libZip->open($zip);
        if (!$libZip->extractTo($dir)) {
            $this->setError($libZip->getError());
            return false;
        }

		include PATH_ADMIN.DS.'system'.DS.'app.php';
		include $dir.DS.'admin'.DS.'apps'.DS.$app->name.'.php';
		
		$appClass = 'app_'.$app->name;
		$appObj = new $appClass();
		$appObj->setName($app->name);
		$appObj->install();

		$adminConfigSystem = Be::getConfig('System.admin');
        $serviceSystem = Be::getService('system');
		if (!in_array($app->name, $adminConfigSystem->apps)) {
			$adminConfigSystem->apps[] = $app->name;
            $serviceSystem->updateConfig($adminConfigSystem, PATH_DATA.DS.'adminConfig'.DS.'system.php');
		}

		// 删除临时文件
		unlink($zip);

		$libFso = Be::getLib('fso');
		$libFso->rmDir($dir);

		return true;
    }
    

    // 删除应用
    public function uninstall($name)
    {
		$adminConfigSystem = Be::getConfig('System.admin');

		$apps = array();
		foreach ($adminConfigSystem->apps as $app) {
			if ($app!=$name) {
				$apps[] = $app;
			}
		}

		$adminConfigSystem->apps = $apps;
        Be::getService('system')->updateConfig($adminConfigSystem, PATH_DATA.DS.'adminConfig'.DS.'system.php');

		$app = Be::getApp($name);
		$app->uninstall();

        return true;
    }

}
