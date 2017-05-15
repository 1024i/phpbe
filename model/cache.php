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
            return $this->clear('admin_template')
                && $this->clear('file')
                && $this->clear('html')
                && $this->clear('menu')
                && $this->clear('role')
                && $this->clear('row')
                && $this->clear('table')
                && $this->clear('template');
        }

        $lib_fso = be::get_lib('fso');
        if ($file === null) return $lib_fso->rm_dir(PATH_DATA . DS . 'cache' . DS . $dir);
        return $lib_fso->rm_dir(PATH_DATA . DS . 'system' . DS . 'cache' . DS . $dir . DS . $file);
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

        if (!db::get_result('SHOW TABLES LIKE \'' . $row_name . '\'')) {
            $row_name = 'be_' . $row_name;
            if (!db::get_result('SHOW TABLES LIKE \'' . $row_name . '\'')) {
                $this->set_error('未找到名称为 ' . $name . ' 的数据库表！');
                return false;
            }
        }

        $primary_key = 'id';
        $fields = db::get_objects('SHOW FULL FIELDS FROM ' . $row_name);

        $code = '<?php' . "\n";
        $code .= 'namespace data\\system\\cache\\row;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $name . ' extends \\system\\row' . "\n";
        $code .= '{' . "\n";

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
                $val = $field->Default ? $field->Default : 0;
            } else {
                $val = $field->Default ? ('\'' . addslashes($field->Default) . '\'') : '\'\'';
            }

            $code .= '    public $' . $field->Field . ' = ' . $val . ';';
            if ($field->Comment) $code .= ' // ' . $field->Comment;
            $code .= "\n";
        }

        $code .= "\n";
        $code .= '    public function __construct()' . "\n";
        $code .= '    {' . "\n";
        $code .= '        parent::__construct(\'' . $row_name . '\', \'' . $primary_key . '\');' . "\n";
        $code .= '    }' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'row' . DS . $name . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

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

        if (!db::get_result('SHOW TABLES LIKE \'' . $table_name . '\'')) {
            $table_name = 'be_' . $table_name;
            if (!db::get_result('SHOW TABLES LIKE \'' . $table_name . '\'')) {
                $this->set_error('未找到名称为 ' . $name . ' 的数据库表！');
                return false;
            }
        }

        $fields = db::get_objects('SHOW FULL FIELDS FROM ' . $table_name);
        $primary_key = 'id';
        $field_names = array();
        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primary_key = $field->Field;
            }

            $field_names[] = $field->Field;
        }

        $code = '<?php' . "\n";
        $code .= 'namespace data\\system\\cache\\table;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $name . ' extends \\system\\table' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $table_name = \'' . $table_name . '\'; // 表名' . "\n";
        $code .= '    protected $primary_key = \'' . $primary_key . '\'; // 主键' . "\n";
        $code .= '    protected $fields = [\'' . implode('\', \'', $field_names) . '\']; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'table' . DS . $name . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

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
        $row->load(array('class_name' => $menu_name));
        if (!$row->id) {
            $this->set_error('未找到调用类名为 ' . $menu_name . ' 的菜单！');
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
            $this->set_error('未找到指定编号（#' . $role_id . '）的角色！');
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
        $row->load(array('class' => $class));
        if (!$row->id) {
            $this->set_error('未找到调用类名为 ' . $class . ' 的 html 内容！');
            return false;
        }

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html' . DS . $class . '.html';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $row->body, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新模板
     *
     * @param string $theme 主题名
     * @param string $template 模析名
     * @param bool $admin 是否是后台模析
     * @return bool 是否更新成功
     */
    public function update_template($theme, $template, $admin = false)
    {
        $file_theme = ($admin ? PATH_ADMIN : PATH_ROOT) . DS . 'theme' . DS . $theme . DS . $theme . '.php';
        if (!file_exists($file_theme)) {
            $this->set_error('主题 ' . $theme . ' 不存在！');
            return false;
        }

        $file_template = ($admin ? PATH_ADMIN : PATH_ROOT) . DS . 'template' . DS . str_replace('.', DS, $template) . '.php';
        if (!file_exists($file_template)) {
            $this->set_error('模板 ' . $template . ' 不存在！');
            return false;
        }

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . ($admin ? 'admin_template' : 'template') . DS . '_' . $theme . DS . str_replace('.', DS, $template) . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $content_theme = file_get_contents($file_theme);
        $content_template = file_get_contents($file_template);

        $code_pre = '';
        $code_use = '';
        $code_html = '';
        $pattern = '/<!--{html}-->(.*?)<!--{\/html}-->/s';
        if (preg_match($pattern, $content_template, $matches)) { // 查找替换 html
            $code_html = $matches[1];

            if (preg_match_all('/use\s(.+);/', $content_template, $matches)) {
                foreach ($matches[1] as $m) {
                    $code_use .= 'use ' . $m . ';' . "\n";
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{html}-->/s';
            if (preg_match($pattern, $content_template, $matches)) {
                $code_pre = trim($matches[1]);
                $code_pre = preg_replace('/use\s(.+);/', '', $code_pre);
                $code_pre = preg_replace('/\s+$/m', '', $code_pre);
            }

        } else {

            if (preg_match($pattern, $content_theme, $matches)) {
                $code_html = $matches[1];

                $pattern = '/<!--{head}-->(.*?)<!--{\/head}-->/s';
                if (preg_match($pattern, $content_template, $matches)) { // 查找替换 head
                    $code_head = $matches[1];
                    $code_html = preg_replace($pattern, $code_head, $code_html);
                }

                $pattern = '/<!--{body}-->(.*?)<!--{\/body}-->/s';
                if (preg_match($pattern, $content_template, $matches)) { // 查找替换 body
                    $code_body = $matches[1];
                    $code_html = preg_replace($pattern, $code_body, $code_html);
                } else {

                    $pattern = '/<!--{north}-->(.*?)<!--{\/north}-->/s';
                    if (preg_match($pattern, $content_template, $matches)) { // 查找替换 north
                        $code_north = $matches[1];
                        $code_html = preg_replace($pattern, $code_north, $code_html);
                    }

                    $pattern = '/<!--{middle}-->(.*?)<!--{\/middle}-->/s';
                    if (preg_match($pattern, $content_template, $matches)) { // 查找替换 north
                        $code_middle = $matches[1];
                        $code_html = preg_replace($pattern, $code_middle, $code_html);
                    } else {
                        $pattern = '/<!--{west}-->(.*?)<!--{\/west}-->/s';
                        if (preg_match($pattern, $content_template, $matches)) { // 查找替换 west
                            $code_west = $matches[1];
                            $code_html = preg_replace($pattern, $code_west, $code_html);
                        }

                        $pattern = '/<!--{center}-->(.*?)<!--{\/center}-->/s';
                        if (preg_match($pattern, $content_template, $matches)) { // 查找替换 center
                            $code_center = $matches[1];
                            $code_html = preg_replace($pattern, $code_center, $code_html);
                        }

                        $pattern = '/<!--{east}-->(.*?)<!--{\/east}-->/s';
                        if (preg_match($pattern, $content_template, $matches)) { // 查找替换 east
                            $code_east = $matches[1];
                            $code_html = preg_replace($pattern, $code_east, $code_html);
                        }
                    }

                    $pattern = '/<!--{message}-->(.*?)<!--{\/message}-->/s';
                    if (preg_match($pattern, $content_template, $matches)) { // 查找替换 message
                        $code_message = $matches[1];
                        $code_html = preg_replace($pattern, $code_message, $code_html);
                    }

                    $pattern = '/<!--{south}-->(.*?)<!--{\/south}-->/s';
                    if (preg_match($pattern, $content_template, $matches)) { // 查找替换 north
                        $code_south = $matches[1];
                        $code_html = preg_replace($pattern, $code_south, $code_html);
                    }
                }
            }

            $pattern = '/use\s(.+);/';
            $uses =null;
            if (preg_match_all($pattern, $content_theme, $matches)) {
                $uses = $matches[1];
                foreach ($matches[1] as $m) {
                    $code_use .= 'use ' . $m . ';' . "\n";
                }
            }

            if (preg_match_all($pattern, $content_template, $matches)) {
                foreach ($matches[1] as $m) {
                    if ($uses !== null && !in_array($m, $uses)) {
                        $code_use .= 'use ' . $m . ';' . "\n";
                    }
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{html}-->/s';
            if (preg_match($pattern, $content_theme, $matches)) {
                $code_pre_theme = trim($matches[1]);
                $code_pre_theme = preg_replace('/use\s(.+);/', '', $code_pre_theme);
                $code_pre_theme = preg_replace('/\s+$/m', '', $code_pre_theme);

                $code_pre = $code_pre_theme . "\n" ;
            }

            if (preg_match($pattern, $content_template, $matches)) {
                $code_pre_template = trim($matches[1]);
                $code_pre_template = preg_replace('/use\s(.+);/', '', $code_pre_template);
                $code_pre_template = preg_replace('/\s+$/m', '', $code_pre_template);

                $code_pre .= $code_pre_template . "\n";
            }
        }

        $templates = explode('.', $template);
        $class_name = array_pop($templates);

        $namespace_suffix = '';
        if (count($templates)) {
            $namespace_suffix = '\\' . implode('\\', $templates);
        }

        $code_php = '<?php' . "\n";
        $code_php .= 'namespace data\\system\\cache\\' . ($admin ? 'admin_template' : 'template') . '\\_' . $theme . $namespace_suffix . ';' . "\n";
        $code_php .= "\n";
        $code_php .= $code_use;
        $code_php .= "\n";
        $code_php .= 'class ' . $class_name . "\n";
        $code_php .= '{' . "\n";
        $code_php .= '  public function display()' . "\n";
        $code_php .= '  {' . "\n";
        $code_php .= $code_pre;
        $code_php .= '    ?>' . "\n";
        $code_php .= $code_html . "\n";
        $code_php .= '    <?php' . "\n";
        $code_php .= '  }' . "\n";
        $code_php .= '}' . "\n";
        $code_php .= "\n";

        file_put_contents($path, $code_php, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新后台模板
     *
     * @param string $theme 主题名
     * @param string $template 模析名
     * @return bool 是否更新成功
     */
    public function update_admin_template($theme, $template)
    {
        return $this->update_template($theme, $template, true);
    }
}
