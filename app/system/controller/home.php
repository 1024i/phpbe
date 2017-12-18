<?php
namespace controller;

use system\be;
use system\response;

class home extends \system\controller
{

	public function home()
	{
		$config_system = be::get_config('system.system');
		response::set_title($config_system->home_title);
        response::set_meta_keywords($config_system->home_meta_keywords);
        response::set_meta_description($config_system->home_meta_description);
        response::display();
	}

}
?>