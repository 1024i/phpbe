<?php
namespace controller;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Db;

class Setup extends \Phpbe\System\Controller
{

	public function __construct()
	{
		$task = Request::_('task');
		if ($task!='complete') {
			db::connect();
			if (!db::hasError()) {
				$rows = db::getTables();
				//printR($rows);
				//if (in_array($rows, 'beUser')) $this->redirect(url('controller=setup&task=complete'));
			}
		}
	}

	public function index()
	{
		$this->setting();
	}


	public function setting()	// 配置数据库
	{
		$template = Be::getTemplate('setup.setting');
		$template->setTitle('配置数据库');
		$template->display();
	}


	public function settingSave()	// 保存配置
	{
		$configDb = Be::getConfig('db');
		$configDb->dbHost = Request::post('dbHost', '');
		$configDb->dbUser = Request::post('dbUser', '');
		$configDb->dbPass = Request::post('dbPass', '');
		$configDb->dbName = Request::post('dbName', '');

		$serviceSetup = Be::getService('setup');
		$serviceSetup->saveConfig($configDb, Be::getRuntime()->getPathRoot() . '/configs/db.php');

		db::connect();
		if (db::hasError()) {
			$this->setMessage(db::getError(), 'error');
			$this->redirect(url('controller=setup&task=setting'));
		} else {
			$serviceSetup->install();
			$this->redirect(url('controller=setup&task=complete'));
		}
	}


	public function complete()
	{
		$template = Be::getTemplate('setup.complete');
		$template->setTitle('完成配置');
		$template->display();
		
		/*
		$path = BONE_ROOT'/setup.html';
		if (file_exists($path)) @unlink($path);

		$path = BONE_ROOT'/apps/setup';
		if (file_exists($path)) {
			$fso = Be::getLib('fso');
			$fso->rmDir($path);
		}
		*/

	}




}
