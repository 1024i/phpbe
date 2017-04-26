<?php
namespace admin\model;

use \system\be;
use \system\db;

class dev extends \system\model
{

	/**
	 * 获取 数据库表 和 系统table 变动情况
	 *
	 *
	 * @return array
	 *
	 * @author Lou Barnes
	 */
    public function get_db_tables()
    {
		$formatted_db_tables = array();

        $db_tables = db::get_tables();

		foreach ($db_tables as $db_table) {
			$db_table = (array)$db_table;
			$db_table_name = array_shift($db_table);
			
			$system_table_name = $db_table_name;
			if (substr($db_table_name, 0, 3) == 'be_') $system_table_name = substr($db_table_name, 3);

			$formatted_db_table = new \stdClass();
			$formatted_db_table->db_table_name = $db_table_name;
			$formatted_db_table->system_table_name = $system_table_name;

			$formatted_db_table->all_db_table_fields_exists = 0;
			$formatted_db_table->all_system_table_fields_exists = 0;
			$formatted_db_table->db_table_fields = array();
			$formatted_db_table->system_table_fields = array();

			$db_table_fields = db::get_table_fields($db_table_name);
			$formatted_db_table_fields = array();
			foreach ($db_table_fields as $db_table_field) {
				$formatted_db_table_fields[] = $db_table_field->Field;
			}
			$formatted_db_table->db_table_fields = $formatted_db_table_fields;


			$formatted_db_table->exists = 1;
			$system_table = be::get_row($system_table_name);
			if (!$system_table) {
				$system_table = be::get_row($system_table_name);
				if (!$system_table) {
					$formatted_db_table->exists = 0;
					$formatted_db_tables[] = $formatted_db_table;
					continue;
				}
			}

			$system_table_fields = get_object_vars($system_table);
			$formatted_system_table_fields = array();
			foreach ($system_table_fields as $system_table_field=>$val) {

				if (substr($system_table_field, 0, 1) == '_') continue;
				$formatted_system_table_fields[] = $system_table_field;
			}
			$formatted_db_table->system_table_fields = $formatted_system_table_fields;

			$all_db_table_fields_exists = 1;
			foreach ($formatted_db_table_fields as $x) {
				if (!in_array($x, $formatted_system_table_fields)) {
					$all_db_table_fields_exists = 0;
					break;
				}
			}
			$formatted_db_table->all_db_table_fields_exists = $all_db_table_fields_exists;

			$all_system_table_fields_exists = 1;
			foreach ($formatted_system_table_fields as $x) {
				if (!in_array($x, $formatted_db_table_fields)) {
					$all_system_table_fields_exists = 0;
					break;
				}
			}
			$formatted_db_table->all_system_table_fields_exists = $all_system_table_fields_exists;

			$formatted_db_tables[] = $formatted_db_table;
		}

		return $formatted_db_tables;
    }



	/**
	 * 获取单个表的 数据库 和 系统 table 表 情况
	 *
	 * @param string $db_table_name 表名
	 *
	 * @return object
	 *
	 * @author Lou Barnes
	 */
	public function get_db_table($db_table_name)
	{
		$db_table = new \stdClass();

		$system_table_name = $db_table_name;
		if (substr($db_table_name, 0, 3) == 'be_') $system_table_name = substr($db_table_name, 3);

		$db_table->db_table_name = $db_table_name;
		$db_table->system_table_name = $system_table_name;

		$db_table_primary_key = 'id';

		$db_table_fields = db::get_table_fields($db_table_name);
		//print_r($db_table_fields);

		$formatted_db_table_fields = array();
		foreach ($db_table_fields as $db_table_field) {
			if ($db_table_field->Key == 'PRI') {
				$db_table_primary_key = $db_table_field->Field;
			}

			$number_types = array('int', 'tinyint', 'smallint', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial');

			$is_number = 0;
			foreach ($number_types as $number_type) {
				if (substr($db_table_field->Type, 0, strlen($number_type)) == $number_type) {
					$is_number = 1;
					break;
				}
			}

			$val = '\'\'';
			if ($is_number) $val = 0;

			$formatted_db_table_fields[] = array($db_table_field->Field, $val);
		}
		$db_table->primary_key = $db_table_primary_key;
		$db_table->db_table_fields = $formatted_db_table_fields;

		$system_table_code = '';
		$system_table_admin_code = '';
		
		$system_table_name = $db_table_name;
		if (substr($db_table_name, 0, 3) == 'be_') $system_table_name = substr($db_table_name, 3);

		$system_table = be::get_row($system_table_name);
		if ($system_table) {
			$system_table_code = file_get_contents(PATH_ROOT.DS.'tables'.DS.$system_table_name.'.php');
		} else {
			$system_table = be::get_row($system_table_name);
			if ($system_table) {
				$system_table_admin_code = file_get_contents(PATH_ADMIN.DS.'tables'.DS.$system_table_name.'.php');
			}
		}


		$formatted_system_table_fields = array();
		if ($system_table) {
			$system_table_fields = get_object_vars($system_table);
			foreach ($system_table_fields as $system_table_field=>$val) {
				if (substr($system_table_field, 0, 1) == '_') continue;
				$formatted_system_table_fields[] = array($system_table_field, $val);
			}
		}
		$db_table->system_table_fields = $formatted_system_table_fields;

		$db_table->db_table_code = $this->get_system_table_code('table_'.$system_table_name, $db_table_name, $db_table_primary_key, $formatted_db_table_fields);
		$db_table->db_table_admin_code = $this->get_system_table_code('admin_table_'.$system_table_name, $db_table_name, $db_table_primary_key, $formatted_db_table_fields);

		$db_table->system_table_code = $system_table_code;
		$db_table->system_table_admin_code = $system_table_admin_code;

		return $db_table;
	}


	public function get_system_table_code($system_table_name, $db_table_name, $primary_key, $data)
	{
		$code = '<?php'."\r\n";
		$code .= 'class '.$system_table_name.' extends table'."\r\n";
		$code .= '{'."\r\n";
		foreach ($data as $x) {
			$code .= '  public $'.$x[0].' = '.$x[1].';'."\r\n";
		}
		$code .= "\r\n";

		$code .= '  public function __construct()'."\r\n";
		$code .= '  {'."\r\n";
		$code .= '    parent::__construct(\''.$db_table_name.'\', \''.$primary_key.'\');'."\r\n";
		$code .= '  }'."\r\n";

		$code .= '}'."\r\n";
		$code .= '?>';

		return $code;
	}


}
?>