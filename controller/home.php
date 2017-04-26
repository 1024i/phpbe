<?php
namespace controller;

use system\be;

class home extends \system\controller
{

	public function home()
	{
		$template = be::get_template('home.home');
		
		$config_system = be::get_config('system');
		$template->set_title($config_system->home_title);
		$template->set_meta_keywords($config_system->home_meta_keywords);
		$template->set_meta_description($config_system->home_meta_description);

		$template->display();
	}

}
?>