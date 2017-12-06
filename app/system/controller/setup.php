<?php
namespace controller;

use system\be;
use system\request;
use system\db;

class setup extends \system\controller
{

	public function __construct()
	{
		$task = request::_('task');
		if ($task!='complete') {
			db::connect();
			if (!db::has_error()) {
				$rows = db::get_tables();
				//print_r($rows);
				//if (in_array($rows, 'be_user')) $this->redirect(url('controller=setup&task=complete'));
			}
		}
	}

	public function index()
	{
		$this->setting();
	}


	public function setting()	// 配置数据库
	{
		$template = be::get_template('setup.setting');
		$template->set_title('配置数据库');
		$template->display();
	}


	public function setting_save()	// 保存配置
	{
		$config_db = be::get_config('db');
		$config_db->db_host = request::post('db_host', '');
		$config_db->db_user = request::post('db_user', '');
		$config_db->db_pass = request::post('db_pass', '');
		$config_db->db_name = request::post('db_name', '');

		$service_setup = be::get_service('setup');
		$service_setup->save_config($config_db, PATH_ROOT.DS.'configs'.DS.'db.php');

		db::connect();
		if (db::has_error()) {
			$this->set_message(db::get_error(), 'error');
			$this->redirect(url('controller=setup&task=setting'));
		} else {
			$service_setup->install();
			$this->redirect(url('controller=setup&task=complete'));
		}
	}


	public function complete()
	{
		$template = be::get_template('setup.complete');
		$template->set_title('完成配置');
		$template->display();
		
		/*
		$path = BONE_ROOT.DS.'setup.html';
		if (file_exists($path)) @unlink($path);

		$path = BONE_ROOT.DS.'apps'.DS.'setup';
		if (file_exists($path)) {
			$fso = be::get_lib('fso');
			$fso->rm_dir($path);
		}
		*/

	}




}
?>