<?php
namespace admin\template\dev;

class db_tables extends \admin\theme
{

	protected function head()
	{
		$ui_list = be::get_admin_ui('list');
		$ui_list->head();
	}

	protected function center()
	{
		$db_tables = $this->get('db_tables');

		$ui_list = be::get_admin_ui('list');

		$i = 0;
		foreach ($db_tables as $db_table) {
			$db_table->id = $i++;

			$db_table->html_db_table_name = $db_table->db_table_name.'（'.count($db_table->db_table_fields).'个字段）';

			$html_exists = '';
			if ($db_table->exists) {
				$html_exists .= '<span class="text-success">存在'.'（'.count($db_table->system_table_fields).'个字段）</span>';
			} else {
				$html_exists .= '<span class="text-warning">不存在</span>';
			}
			$db_table->html_exists = $html_exists;


			$editable = false;

			$html_match = '<span class="text-success">正常！</span>';
			if ($db_table->exists) {
				if ($db_table->all_db_table_fields_exists == 0) {
					$editable = true;

					if ($db_table->all_system_table_fields_exists == 0) {
						$html_match = '<span class="text-error">同时有字段缺失和多余！</span>';
					} else {
						$html_match = '<span class="text-warning">有字段缺失！</span>';
					}
				} else {
					if ($db_table->all_system_table_fields_exists == 0) {
						$editable = true;
						$html_match = '<span class="text-error">有字段多余！</span>';
					}
				}
			} else {
				$editable = true;
				$html_match = '<span class="text-muted">-</span>';
			}
			$db_table->html_match = $html_match;

			$html_operation = '';
			if ($editable) {
				$html_operation = '<a href="./?controller=dev&task=db_table_edit&db_table_name='.$db_table->db_table_name.'" class="btn btn-mini btn-success" title="核对">核对</a>';
			}
			$db_table->html_operation = $html_operation;

		}

		$ui_list->set_data($db_tables);

		$ui_list->set_fields(

			array(
				'name'=>'html_db_table_name',
				'label'=>'数据库表名',
				'align'=>'left',
				'width'=>'400'
			),
			array(
				'name'=>'html_exists',
				'label'=>'系统表存在?',
				'align'=>'left',
				'width'=>'200'
			),
			array(
				'name'=>'html_match',
				'label'=>'完全匹配?',
				'align'=>'left'
			),
			array(
				'name'=>'html_operation',
				'label'=>'操作',
				'align'=>'center',
				'width'=>'90'
			)

		);
		$ui_list->display();
	}	

}
?>