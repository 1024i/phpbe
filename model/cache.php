<?php
namespace model;

use \system\be;
use \system\db;

class cache extends \system\model
{

    /**
     * 清除缓存
     *
     * @param string $dir 缓存文件夹名，为 null 时清空所有缓存
     * @param string $file 指定缓存文件夹下的文件名，为 null 时清空整个文件夹
     * @return bool 是否清除成功
     */
    public function clear($dir = null, $file = null)
    {
        if ($dir === null) {
            return $this->clear('menu') && $this->clear('table') && $this->clear('html');
        }

        $lib_fso = be::get_lib('fso');
        if ($file === null) return $lib_fso->rm_dir(PATH_DATA.DS.'cache'.DS.$dir);
        return $lib_fso->rm_dir(PATH_DATA.DS.'system'.DS.'cache'.DS.$dir.DS.$file);
    }

    /**
     * 更新 数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名称
     * @return bool 是否更新成功
     */
    public function update_row($name)
    {
        $row_name = $name;

        if (!db::get_result('SHOW TABLES LIKE \''.$row_name.'\'')) {
            $row_name = 'be_'.$row_name;
            if (!db::get_result('SHOW TABLES LIKE \''.$row_name.'\'')) {
                $this->set_error('未找到名称为 '.$name.' 的数据库表！');
                return false;
            }
        }

        $primary_key = 'id';
        $fields = db::get_objects('SHOW FULL FIELDS FROM '. $row_name);

        $code = '<?php'."\n";
        $code .= 'namespace data\system\cache\row;'."\n";        $code .= "\n";
        $code .= 'class '.$name.' extends \system\row'."\n";
        $code .= '{'."\n";

        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primary_key = $field->Field;
            }

            $number_types = array('int', 'tinyint', 'smallint', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial');

            $is_number = 0;
            foreach ($number_types as $number_type) {
                if (substr($field->Type, 0, strlen($number_type)) == $number_type) {
                    $is_number = 1;
                    break;
                }
            }

            $val = null;
            if ($is_number) {
                $val = $field->Default?$field->Default:0;
            } else {
                $val = $field->Default?('\''.addslashes($field->Default).'\''):'\'\'';
            }

            $code .= '    public $'.$field->Field.' = '.$val.';';
            if ($field->Comment) $code .= ' // '.$field->Comment;
            $code .="\n";
        }

        $code .= "\n";
        $code .= '    public function __construct()'."\n";
        $code .= '    {'."\n";
        $code .= '        parent::__construct(\''.$row_name.'\', \''.$primary_key.'\');'."\n";
        $code .= '    }'."\n";
        $code .= '}'."\n";
        $code .= "\n";

        $path = PATH_DATA.DS.'system'.DS.'cache'.DS.'row'.DS.$name.'.php';
        file_put_contents($path, $code);
        chmod($path,  0755);

        return true;
    }

    /**
     * 更新表
     *
     * @param string $name 要表新的表名
     * @return bool 是否更新成功
     */
    public function update_table($name)
    {
        $table_name = $name;

        if (!db::get_result('SHOW TABLES LIKE \''.$table_name.'\'')) {
            $table_name = 'be_'.$table_name;
            if (!db::get_result('SHOW TABLES LIKE \''.$table_name.'\'')) {
                $this->set_error('未找到名称为 '.$name.' 的数据库表！');
                return false;
            }
        }

        $primary_key = 'id';
        $fields = db::get_objects('SHOW FULL FIELDS FROM '. $table_name);
        $field_names = array();
        foreach ($fields as $field) {
            $field_names[] = $field->Field;
        }

        $code = '<?php'."\n";
        $code .= 'namespace data\system\cache\table;'."\n";
        $code .= "\n";
        $code .= 'class '.$name.' extends \system\table'."\n";
        $code .= '{'."\n";

        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primary_key = $field->Field;
            }
        }

        $code .= '    protected $table_name = \''.$table_name.'\'; // 表名'."\n";
        $code .= '    protected $primary_key = \''.$primary_key.'\'; // 主键'."\n";
        $code .= '    protected $fields = [\''.implode('\', \'', $field_names).'\']; // 字段列表'."\n";
        $code .= '}'."\n";
        $code .= "\n";

        $path = PATH_DATA.DS.'system'.DS.'cache'.DS.'table'.DS.$name.'.php';
        file_put_contents($path, $code);
        chmod($path,  0755);

        return true;
    }

    /**
     * 更新菜单
     *
     * @param string $menu_name 菜单名
     * @return bool 是否更新成功
     */
    public function update_menu($menu_name)
    {
        $row = be::get_row('system_menu_group');
        $row->load(array('class_name'=>$menu_name));
        if (!$row->id) {
            $this->set_error('未找到调用类名为 '.$menu_name.' 的菜单！');
            return false;
        }

        $model = be::get_admin_model('menu');
        if (!$model->update_cache($row->id)) {
            $this->set_error($model->get_error());
            return false;
        }

        return true;
    }

    /**
     * 更新用户角色
     *
     * @param int $role_id 用户角色ID
     * @return bool
     */
    public function update_role($role_id)
    {
        $row = be::get_row('system_menu_group');
        $row->load($role_id);
        if (!$row->id) {
            $this->set_error('未找到指定编号（#'.$role_id.'）的角色！');
            return false;
        }

        $model = be::get_admin_model('role');
        if (!$model->update_cache($row->id)) {
            $this->set_error($model->get_error());
            return false;
        }

        return true;
    }

    /**
     * 更新自定义 html 内容
     *
     * @param string $class 调用类名
     * @return bool 是否更新成功
     */
    public function update_html($class)
    {
        $row = be::get_row('system_html');
        $row->load(array('class'=>$class));
        if (!$row->id) {
            $this->set_error('未找到调用类名为 '.$class.' 的 html 内容！');
            return false;
        }

        $path = PATH_DATA.DS.'system'.DS.'cache'.DS.'html'.DS.$class.'.html';
        file_put_contents($path, $row->body);
        chmod($path,  0755);

        return true;
    }



}
?>