<?php
namespace model;

class install extends \system\model
{


	public function save_config($obj, $file)
	{
		$vars = get_object_vars($obj);

		$buf = "<?php\r\n";
		$buf .= 'class '.get_class($obj)."\r\n";
		$buf .= "{\r\n";

		foreach ($vars as $key=>$val) {
			$buf .= '  public $'.$key.' = \''.$val.'\';' . "\r\n";
		}
		$buf .= "}\r\n";
		$buf .= '?>';
		
		file_put_contents($file, $buf);
	}


	public function install()
	{
		$files = array();

		$files[] = PATH_ADMIN.DS.'apps'.DS.'content'.DS.'install.sql';
		$files[] = PATH_ADMIN.DS.'apps'.DS.'content'.DS.'init.sql';

		$files[] = PATH_ADMIN.DS.'apps'.DS.'menu'.DS.'install.sql';
		$files[] = PATH_ADMIN.DS.'apps'.DS.'menu'.DS.'init.sql';

		$files[] = PATH_ADMIN.DS.'apps'.DS.'user'.DS.'install.sql';
		$files[] = PATH_ADMIN.DS.'apps'.DS.'user'.DS.'init.sql';
		
		$db = be::get_db();
		foreach ($files as $file) {
			if (file_exists($file)) {
				$sqls = $this->split_sql(file_get_contents($file));
				foreach ($sqls as $sql) {
					$db->execute($sql);
				}
			}
		}
	}


}
?>