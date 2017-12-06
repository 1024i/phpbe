<?php
namespace service;

use system\be;
use system\db;

class system extends \system\service
{

    /**
     * 清除缓存
     *
     * @param string $dir 缓存文件夹名，为 null 时清空所有缓存
     * @param string $file 指定缓存文件夹下的文件名，为 null 时清空整个文件夹
     * @return bool 是否清除成功
     */
    public function clear_cache($dir = null, $file = null)
    {
        if ($dir === null) {
            return $this->clear_cache('file')
                && $this->clear_cache('html')
                && $this->clear_cache('menu')
                && $this->clear_cache('user_role')
                && $this->clear_cache('admin_user_role')
                && $this->clear_cache('row')
                && $this->clear_cache('table')
                && $this->clear_cache('template')
                && $this->clear_cache('admin_template');
        }

        $lib_fso = be::get_lib('fso');
        if ($file === null) return $lib_fso->rm_dir(PATH_DATA . DS . 'system' . DS . 'cache' . DS . $dir);
        return $lib_fso->rm_dir(PATH_DATA . DS . 'system' . DS . 'cache' . DS . $dir . DS . $file);
    }

    /**
     * 更新 数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名称
     * @return bool 是否更新成功
     */
    public function update_cache_row($name)
    {
        $row_name = $name;
        $db = be::get_db();
        if (!$db->get_value('SHOW TABLES LIKE \'' . $row_name . '\'')) {
            $row_name = 'be_' . $row_name;
            if (!$db->get_value('SHOW TABLES LIKE \'' . $row_name . '\'')) {
                $this->set_error('未找到名称为 ' . $name . ' 的数据库表！');
                return false;
            }
        }

        $primary_key = 'id';
        $fields = $db->get_objects('SHOW FULL FIELDS FROM ' . $row_name);

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
    public function update_cache_table($name)
    {
        $table_name = $name;
        $db = be::get_db();
        if (!$db->get_value('SHOW TABLES LIKE \'' . $table_name . '\'')) {
            $table_name = 'be_' . $table_name;
            if (!$db->get_value('SHOW TABLES LIKE \'' . $table_name . '\'')) {
                $this->set_error('未找到名称为 ' . $name . ' 的数据库表！');
                return false;
            }
        }

        $fields = $db->get_objects('SHOW FULL FIELDS FROM ' . $table_name);
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
    public function update_cache_menu($menu_name)
    {
        $group = be::get_row('system_menu_group');
        $group->load(array('class_name' => $menu_name));
        if (!$group->id) {
            $this->set_error('未找到调用类名为 ' . $menu_name . ' 的菜单！');
            return false;
        }

        $db = be::get_db();
        $menus = $db->get_objects('SELECT * FROM `be_system_menu` WHERE `group_id`=' . $group->id . ' ORDER BY `rank` ASC');;

        $code = '<?php' . "\n";
        $code .= 'namespace data\system\cache\menu;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $group->class_name . ' extends \system\menu' . "\n";
        $code .= '{' . "\n";
        $code .= '  public function __construct()' . "\n";
        $code .= '  {' . "\n";
        foreach ($menus as $menu) {
            if ($menu->home == 1) {
                $home_params = array();

                $menu_params = $menu->params;
                if ($menu_params == '') $menu_params = $menu->url;

                if (strpos($menu_params, '=')) {
                    $menu_params = explode('&', $menu_params);
                    foreach ($menu_params as $menu_param) {
                        $menu_param = explode('=', $menu_param);
                        if (count($menu_param) == 2) $home_params[$menu_param[0]] = $menu_param[1];
                    }
                }

                $config_system = be::get_config('system');
                if (serialize($config_system->home_params) != serialize($home_params)) {
                    $config_system->home_params = $home_params;
                    $this->save_config($config_system, PATH_ROOT . DS . 'config' . DS . 'system.php');
                }
            }

            $params = array();

            $menu_params = $menu->params;
            if ($menu_params == '') $menu_params = $menu->url;

            if (strpos($menu_params, '=')) {
                $menu_params = explode('&', $menu_params);
                foreach ($menu_params as $menu_param) {
                    $menu_param = explode('=', $menu_param);
                    if (count($menu_param) == 2) $params[] = '\'' . $menu_param[0] . '\'=>\'' . $menu_param[1] . '\'';
                }
            }

            $param = 'array(' . implode(',', $params) . ')';

            $url = $menu->url;
            if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') {
                $url = '\'' . $url . '\'';
            } else {
                $url = 'url(\'' . $url . '\')';
            }

            $code .= '    $this->add_menu(' . $menu->id . ', ' . $menu->parent_id . ', \'' . $menu->name . '\', ' . $url . ', \'' . $menu->target . '\', ' . $param . ', ' . $menu->home . ');' . "\n";
        }
        $code .= '  }' . "\n";
        $code .= '}' . "\n";

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'menu' . DS . $group->class_name . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新前台用户角色
     *
     * @param int $role_id 用户角色ID
     * @return bool
     */
    public function update_cache_user_role($role_id)
    {
        $row = be::get_row('user_role');
        $row->load($role_id);
        if (!$row->id) {
            $this->set_error('未找到指定编号（#' . $role_id . '）的用户角色！');
            return false;
        }

        $code = '<?php' . "\n";
        $code .= 'namespace data\system\cache\user_role;' . "\n";
        $code .= "\n";
        $code .= 'class user_role_' . $role_id . ' extends \system\role' . "\n";
        $code .= '{' . "\n";
        $code .= '  public $name = \''.$row->name.'\';' . "\n";
        $code .= '  public $permission = \''.$row->permission.'\';' . "\n";
        $code .= '  public $permissions = [\''.implode('\',\'', explode(',', $row->permissions)).'\'];' . "\n";
        $code .= '}' . "\n";

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'user_role' . DS . 'user_role_' . $role_id . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新后台管理员角色
     *
     * @param int $role_id 管理员角色ID
     * @return bool
     */
    public function update_cache_admin_user_role($role_id)
    {
        $row = be::get_row('admin_user_role');
        $row->load($role_id);
        if (!$row->id) {
            $this->set_error('未找到指定编号（#' . $role_id . '）的管理员角色！');
            return false;
        }

        $code = '<?php' . "\n";
        $code .= 'namespace data\system\cache\admin_user_role;' . "\n";
        $code .= "\n";
        $code .= 'class admin_user_role_' . $role_id . ' extends \system\role' . "\n";
        $code .= '{' . "\n";
        $code .= '  public $name = \''.$row->name.'\';' . "\n";
        $code .= '  public $permission = \''.$row->permission.'\';' . "\n";
        $code .= '  public $permissions = [\''.implode('\',\'', explode(',', $row->permissions)).'\'];' . "\n";
        $code .= '}' . "\n";

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'admin_user_role' . DS . 'admin_user_role_' . $role_id . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新自定义 html 内容
     *
     * @param string $class 调用类名
     * @return bool 是否更新成功
     */
    public function update_cache_html($class)
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
    public function update_cache_template($theme, $template, $admin = false)
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

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . ($admin ? 'admin_template' : 'template') . DS . $theme . DS . str_replace('.', DS, $template) . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $content_theme = file_get_contents($file_theme);
        $content_template = file_get_contents($file_template);

        $code_pre = '';
        $code_use = '';
        $code_html = '';
        $pattern = '/<!--{html}-->(.*?)<!--{\/html}-->/s';
        if (preg_match($pattern, $content_template, $matches)) { // 查找替换 html
            $code_html = trim($matches[1]);

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
                $code_html = trim($matches[1]);

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

            $pattern = '/<\?php(.*?)\?>\s+<!--{(?:html|head|body|north|middle|west|center|east|south|message)}-->/s';
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

        $code_vars = '';
        $config_path = ($admin ? PATH_ADMIN : PATH_ROOT) . DS . 'theme' . DS . $theme . DS . 'config.php';
        if (file_exists($config_path)) {
            include $config_path;
            $theme_config_class_name = ($admin ? 'admin\\' : '') . 'theme\\' . $theme.'\\config';
            if (class_exists($theme_config_class_name)) {
                $theme_config = new $theme_config_class_name();
                if (isset($theme_config->colors) && is_array($theme_config->colors)) {
                    $code_vars .= '  public $colors = [\''.implode('\',\'', $theme_config->colors).'\'];' . "\n";
                }
            }
        }

        $code_php = '<?php' . "\n";
        $code_php .= 'namespace data\\system\\cache\\' . ($admin ? 'admin_template' : 'template') . '\\' . $theme . $namespace_suffix . ';' . "\n";
        $code_php .= "\n";
        $code_php .= $code_use;
        $code_php .= "\n";
        $code_php .= 'class ' . $class_name . ' extends \\system\\template' . "\n";
        $code_php .= '{' . "\n";
        $code_php .= $code_vars;
        $code_php .= "\n";
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
    public function update_cache_admin_template($theme, $template)
    {
        return $this->update_cache_template($theme, $template, true);
    }


    /*
     * 保存配置文件到指定咱径
     *
     * @param object $config 配置文件类
     * @param string $path 保存路径
     *
     * @return bool 是否保存成功
     */
    public function update_config($config, $path)
    {
        $comments = array();
        if (file_exists($path)) {
            $f = fopen($path, 'r');
            while (!feof($f)) {
                $line = fgets($f, 4096);
                $line = trim($line);
                if (strlen($line) > 8 && strtolower(substr($line, 0, 8)) == 'public $') {
                    $key_start_pos = strpos($line, '$');
                    $key_end_pos = strpos($line, '=');
                    if ($key_start_pos !== false && $key_end_pos !== false) {
                        $key = substr($line, $key_start_pos + 1, $key_end_pos - 1 - $key_start_pos);
                        $key = trim($key);

                        $comment_pos = strrpos($line, '//');
                        if ($comment_pos !== false) {
                            $comment = substr($line, $comment_pos + 2);
                            $comment = trim($comment);

                            if (substr($comment, -1, 1) != ';') $comments[$key] = $comment;
                        }
                    }
                }
            }
            fclose($f);
        }

        $vars = get_object_vars($config);

        $class = get_class($config);

        $namespace = substr($class, 0, strrpos($class, '\\'));
        $class_name = substr($class, strrpos($class, '\\') + 1);

        $buf = "<?php\n";
        $buf .= 'namespace '.$namespace.';'. "\n\n";
        $buf .= 'class ' . $class_name . "\n";
        $buf .= "{\r\n";

        foreach ($vars as $key => $val) {
            if (is_array($val)) {
                $indexed = true;

                $i = 0;
                foreach ($val as $index => $x) {
                    if ($i !== $index) {
                        $indexed = false;
                        break;
                    }
                    $i++;
                }

                $arr = array();
                foreach ($val as $index => $x) {
                    $x = str_replace('\'', '&#039;', $x);

                    // 数组含有非数字的索引
                    if ($indexed) {
                        $arr[] = '\'' . $x . '\'';
                    } else {
                        $arr[] = '\'' . $index . '\'=>\'' . $x . '\'';
                    }
                }
                $buf .= '  public $' . $key . ' = [' . implode(', ', $arr) . '];';
            } elseif (is_bool($val)) {
                $buf .= '  public $' . $key . ' = ' . ($val ? 'true' : 'false') . ';';
            } elseif (is_int($val) || is_float($val)) {
                $buf .= '  public $' . $key . ' = ' . $val . ';';
            } else {
                $val = str_replace('\'', '&#039;', $val);
                $buf .= '  public $' . $key . ' = \'' . $val . '\';';
            }

            if (array_key_exists($key, $comments)) $buf .= '  // ' . $comments[$key];

            $buf .= "\n";
        }
        $buf .= "}\n";

        return file_put_contents($path, $buf);
    }


    public function update_cache_config($app) {


    }

}
