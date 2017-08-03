<?php
namespace admin\controller;

class dev extends \admin\controller
{
	public function db_tables()
	{
		$admin_model = be::get_admin_service('dev');
		$db_tables = $admin_model->get_db_tables();
		/*
		foreach ($db_tables as $db_table) {
			echo 'TRUNCATE TABLE `'.$db_table->db_table_name.'`;'."\r\n";
		}
		exit;
		*/

		$admin_template = be::get_admin_template('dev.db_tables');
		$admin_template->set('db_tables', $db_tables);
		$admin_template->display();
	}

	public function db_table_edit()
	{
		$db_table_name = request::get('db_table_name','');

		$admin_model = be::get_admin_service('dev');
		$db_table = $admin_model->get_db_table($db_table_name);
		//print_r($db_table);

		$admin_template = be::get_admin_template('dev.db_table_edit');
		$admin_template->set('db_table', $db_table);
		$admin_template->display();
	}


	public function db_table_edit_save()
	{
		$type = request::post('type', '');
		$file_name = request::post('file_name', '');
		$data = post::html('data', '');

		if ($type == '1') {
			$path = PATH_ROOT.DS.'tables'.DS.$file_name.'.php';
			if (file_exists($path)) {
				$bak_path = PATH_ROOT.DS.'tables'.DS.$file_name.'_'.date('YmdHis').'.bak';
				rename($path, $bak_path);
			}
			file_put_contents($path, $data);
		}

		if ($type == '2') {
			$path = PATH_ADMIN.DS.'tables'.DS.$file_name.'.php';
			if (file_exists($path)) {
				$bak_path = PATH_ADMIN.DS.'tables'.DS.$file_name.'_'.date('YmdHis').'.bak';
				rename($path, $bak_path);
			}
			file_put_contents($path, $data);
		}

		$this->set_message('保存成功！');
		$this->redirect('./?controller=dev&task=db_tables');
	}

}
?>