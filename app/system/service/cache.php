<?php

namespace App\System\Service;

use Phpbe\Util\String;
use Phpbe\Util\System;
use Phpbe\System\Be;
use Phpbe\System\Db;
use Phpbe\System\db\Exception;

class Cache extends \Phpbe\System\Service
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
            return $this->clear('File')
                && $this->clear('Html')
                && $this->clear('Menu')
                && $this->clear('UserRole')
                && $this->clear('AdminUserRole')
                && $this->clear('Row')
                && $this->clear('Table')
                && $this->clear('Template')
                && $this->clear('AdminTemplate');
        }

        $libFso = Be::getLib('Fso');
        if ($file === null) return $libFso->rmDir(PATH_CACHE . DS . $dir);
        return $libFso->rmDir(PATH_CACHE . DS . $dir . '/' . $file);
    }

    /**
     * 更新 数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名称
     * @return bool 是否更新成功
     * @throws |Exception
     */
    public function updateRow($app, $name)
    {
        $tableName = String::snakeCase($app) . '_' . String::snakeCase($name);
        $db = Be::getDb();
        if (!$db->getValue('SHOW TABLES LIKE \'' . $tableName . '\'')) {
            throw new \Exception('未找到名称为 ' . $tableName . ' 的数据库表！');
        }

        $primaryKey = 'id';
        $fields = $db->getObjects('SHOW FULL FIELDS FROM ' . $tableName);

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\Row\\' . $app . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $name . ' extends \\System\\Row' . "\n";
        $code .= '{' . "\n";

        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primaryKey = $field->Field;
            }

            $numberTypes = array('int', 'tinyint', 'smallint', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial');

            $isNumber = 0;
            foreach ($numberTypes as $numberType) {
                if (substr($field->Type, 0, strlen($numberType)) == $numberType) {
                    $isNumber = 1;
                    break;
                }
            }

            $val = null;
            if ($isNumber) {
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
        $code .= '        parent::__construct(\'' . $tableName . '\', \'' . $primaryKey . '\');' . "\n";
        $code .= '    }' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = PATH_CACHE . '/Row/' . $app . '/' . $name . '.php';
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
    public function updateTable($app, $name)
    {
        $tableName = String::snakeCase($app) . '_' . String::snakeCase($name);
        $db = Be::getDb();
        $fields = $db->getObjects('SHOW FULL FIELDS FROM ' . $tableName);
        $primaryKey = 'id';
        $fieldNames = array();
        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primaryKey = $field->Field;
            }

            $fieldNames[] = $field->Field;
        }

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\Table\\' . $app . ';' . "\n";
        $code .= "\n";
        $code .= 'class ' . $name . ' extends \\System\\Table' . "\n";
        $code .= '{' . "\n";
        $code .= '    protected $tableName = \'' . $tableName . '\'; // 表名' . "\n";
        $code .= '    protected $primaryKey = \'' . $primaryKey . '\'; // 主键' . "\n";
        $code .= '    protected $fields = [\'' . implode('\', \'', $fieldNames) . '\']; // 字段列表' . "\n";
        $code .= '}' . "\n";
        $code .= "\n";

        $path = PATH_CACHE . '/Table/' . $app . '/' . $name . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新菜单
     *
     * @param string $menuName 菜单名
     * @return bool 是否更新成功
     */
    public function updateMenu($menuName)
    {
        $group = Be::getRow('System.MenuGroup');
        $group->load(array('className' => $menuName));
        if (!$group->id) {
            $this->setError('未找到调用类名为 ' . $menuName . ' 的菜单！');
            return false;
        }

        $menus = Be::getTable('System.Menu')
            ->where('group_id', $group->id)
            ->orderBy('ordering', 'ASC')
            ->getObjects();

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\Menu;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $group->className . ' extends \\System\\Menu' . "\n";
        $code .= '{' . "\n";
        $code .= '  public function __construct()' . "\n";
        $code .= '  {' . "\n";
        foreach ($menus as $menu) {
            if ($menu->home == 1) {
                $homeParams = array();

                $menuParams = $menu->params;
                if ($menuParams == '') $menuParams = $menu->url;

                if (strpos($menuParams, '=')) {
                    $menuParams = explode('&', $menuParams);
                    foreach ($menuParams as $menuParam) {
                        $menuParam = explode('=', $menuParam);
                        if (count($menuParam) == 2) $homeParams[$menuParam[0]] = $menuParam[1];
                    }
                }

                $configSystem = Be::getConfig('System.System');
                if (serialize($configSystem->homeParams) != serialize($homeParams)) {
                    $configSystem->homeParams = $homeParams;
                    $this->updateConfig($configSystem, Be::getRuntime()->getPathRoot() . '/config/system.php');
                }
            }

            $params = array();

            $menuParams = $menu->params;
            if ($menuParams == '') $menuParams = $menu->url;

            if (strpos($menuParams, '=')) {
                $menuParams = explode('&', $menuParams);
                foreach ($menuParams as $menuParam) {
                    $menuParam = explode('=', $menuParam);
                    if (count($menuParam) == 2) $params[] = '\'' . $menuParam[0] . '\'=>\'' . $menuParam[1] . '\'';
                }
            }

            $param = 'array(' . implode(',', $params) . ')';

            $url = $menu->url;
            if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') {
                $url = '\'' . $url . '\'';
            } else {
                $url = 'url(\'' . $url . '\')';
            }

            $code .= '    $this->addMenu(' . $menu->id . ', ' . $menu->parentId . ', \'' . $menu->name . '\', ' . $url . ', \'' . $menu->target . '\', ' . $param . ', ' . $menu->home . ');' . "\n";
        }
        $code .= '  }' . "\n";
        $code .= '}' . "\n";

        $path = PATH_CACHE . '/Menu/' . $group->className . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新前台用户角色
     *
     * @param int $roleId 用户角色ID
     * @return bool
     */
    public function updateUserRole($roleId)
    {
        $row = Be::getRow('System.UserRole');
        $row->load($roleId);
        if (!$row->id) {
            $this->setError('未找到指定编号（#' . $roleId . '）的用户角色！');
            return false;
        }

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\UserRole;' . "\n";
        $code .= "\n";
        $code .= 'class UserRole' . $roleId . ' extends \\System\\Role' . "\n";
        $code .= '{' . "\n";
        $code .= '  public $name = \'' . $row->name . '\';' . "\n";
        $code .= '  public $permission = \'' . $row->permission . '\';' . "\n";
        $code .= '  public $permissions = [\'' . implode('\',\'', explode(',', $row->permissions)) . '\'];' . "\n";
        $code .= '}' . "\n";

        $path = PATH_CACHE . '/UserRole/UserRole' . $roleId . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新后台管理员角色
     *
     * @param int $roleId 管理员角色ID
     * @return bool
     */
    public function updateAdminUserRole($roleId)
    {
        $row = Be::getRow('System.AdminUserRole');
        $row->load($roleId);
        if (!$row->id) {
            $this->setError('未找到指定编号（#' . $roleId . '）的管理员角色！');
            return false;
        }

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\AdminUserRole;' . "\n";
        $code .= "\n";
        $code .= 'class AdminUserRole' . $roleId . ' extends \\System\\Role' . "\n";
        $code .= '{' . "\n";
        $code .= '  public $name = \'' . $row->name . '\';' . "\n";
        $code .= '  public $permission = \'' . $row->permission . '\';' . "\n";
        $code .= '  public $permissions = [\'' . implode('\',\'', explode(',', $row->permissions)) . '\'];' . "\n";
        $code .= '}' . "\n";

        $path = PATH_CACHE . '/AdminUserRole/AdminUserRole' . $roleId . '.php';
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
    public function updateHtml($class)
    {
        $row = Be::getRow('System.Html');
        $row->load(array('class' => $class));
        if (!$row->id) {
            $this->setError('未找到调用类名为 ' . $class . ' 的 html 内容！');
            return false;
        }

        $path = PATH_CACHE . '/Html/' . $class . '.html';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $row->body, LOCK_EX);
        chmod($path, 0755);

        return true;
    }


    /**
     * 更新模板
     *
     * @param string $app 应用名
     * @param string $template 模析名
     * @param string $theme 主题名
     * @param bool $admin 是否是后台模析
     * @return bool 是否更新成功
     */
    public function updateTemplate($app, $template, $theme, $admin = false)
    {
        $fileTheme = ($admin ? PATH_ADMIN : Be::getRuntime()->getPathRoot()) . '/theme/' . $theme . '/' . $theme . '.php';
        if (!file_exists($fileTheme)) {
            $this->setError('主题 ' . $theme . ' 不存在！');
            return false;
        }

        $fileTemplate = Be::getRuntime()->getPathRoot() . '/App/' . $app . ($admin ? '/AdminTemplate/' : '/Template/') . str_replace('.', '/', $template) . '.php';
        if (!file_exists($fileTemplate)) {
            $this->setError('模板 ' . $template . ' 不存在！');
            return false;
        }

        $path = PATH_CACHE . DS . ($admin ? 'AdminTemplate' : 'Template') . '/' . $theme . '/' . $app . '/' . str_replace('.', '/', $template) . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $contentTheme = file_get_contents($fileTheme);
        $contentTemplate = file_get_contents($fileTemplate);

        $codePre = '';
        $codeUse = '';
        $codeHtml = '';
        $pattern = '/<!--{html}-->(.*?)<!--{\/html}-->/s';
        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 html
            $codeHtml = trim($matches[1]);

            if (preg_match_all('/use\s(.+);/', $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{html}-->/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePre = trim($matches[1]);
                $codePre = preg_replace('/use\s(.+);/', '', $codePre);
                $codePre = preg_replace('/\s+$/m', '', $codePre);
            }

        } else {

            if (preg_match($pattern, $contentTheme, $matches)) {
                $codeHtml = trim($matches[1]);

                $pattern = '/<!--{head}-->(.*?)<!--{\/head}-->/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 head
                    $codeHead = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeHead, $codeHtml);
                }

                $pattern = '/<!--{body}-->(.*?)<!--{\/body}-->/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 body
                    $codeBody = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeBody, $codeHtml);
                } else {

                    $pattern = '/<!--{north}-->(.*?)<!--{\/north}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeNorth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeNorth, $codeHtml);
                    }

                    $pattern = '/<!--{middle}-->(.*?)<!--{\/middle}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeMiddle = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeMiddle, $codeHtml);
                    } else {
                        $pattern = '/<!--{west}-->(.*?)<!--{\/west}-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 west
                            $codeWest = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeWest, $codeHtml);
                        }

                        $pattern = '/<!--{center}-->(.*?)<!--{\/center}-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 center
                            $codeCenter = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeCenter, $codeHtml);
                        }

                        $pattern = '/<!--{east}-->(.*?)<!--{\/east}-->/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 east
                            $codeEast = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeEast, $codeHtml);
                        }
                    }

                    $pattern = '/<!--{message}-->(.*?)<!--{\/message}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 message
                        $codeMessage = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeMessage, $codeHtml);
                    }

                    $pattern = '/<!--{south}-->(.*?)<!--{\/south}-->/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeSouth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeSouth, $codeHtml);
                    }
                }
            }

            $pattern = '/use\s(.+);/';
            $uses = null;
            if (preg_match_all($pattern, $contentTheme, $matches)) {
                $uses = $matches[1];
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            if (preg_match_all($pattern, $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    if ($uses !== null && !in_array($m, $uses)) {
                        $codeUse .= 'use ' . $m . ';' . "\n";
                    }
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{html}-->/s';
            if (preg_match($pattern, $contentTheme, $matches)) {
                $codePreTheme = trim($matches[1]);
                $codePreTheme = preg_replace('/use\s(.+);/', '', $codePreTheme);
                $codePreTheme = preg_replace('/\s+$/m', '', $codePreTheme);
                $codePre = $codePreTheme . "\n";
            }

            $pattern = '/<\?php(.*?)\?>\s+<!--{(?:html|head|body|north|middle|west|center|east|south|message)}-->/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePreTemplate = trim($matches[1]);
                $codePreTemplate = preg_replace('/use\s(.+);/', '', $codePreTemplate);
                $codePreTemplate = preg_replace('/\s+$/m', '', $codePreTemplate);

                $codePre .= $codePreTemplate . "\n";
            }
        }

        $templates = explode('.', $template);
        $className = array_pop($templates);

        $namespaceSuffix = '';
        if (count($templates)) {
            $namespaceSuffix = '\\' . implode('\\', $templates);
        }

        $codeVars = '';
        $configPath = ($admin ? PATH_ADMIN : Be::getRuntime()->getPathRoot()) . '/theme/' . $theme . '/config.php';
        if (file_exists($configPath)) {
            include $configPath;
            $themeConfigClassName = ($admin ? 'admin\\' : '') . 'theme\\' . $theme . '\\config';
            if (class_exists($themeConfigClassName)) {
                $themeConfig = new $themeConfigClassName();
                if (isset($themeConfig->colors) && is_array($themeConfig->colors)) {
                    $codeVars .= '  public $colors = [\'' . implode('\',\'', $themeConfig->colors) . '\'];' . "\n";
                }
            }
        }

        $codePhp = '<?php' . "\n";
        $codePhp .= 'namespace Cache\\' . ($admin ? 'AdminTemplate' : 'Template') . '\\' . $theme . '\\' . $app . $namespaceSuffix . ';' . "\n";
        $codePhp .= "\n";
        $codePhp .= $codeUse;
        $codePhp .= "\n";
        $codePhp .= 'class ' . $className . ' extends \\System\\Template' . "\n";
        $codePhp .= '{' . "\n";
        $codePhp .= $codeVars;
        $codePhp .= "\n";
        $codePhp .= '  public function display()' . "\n";
        $codePhp .= '  {' . "\n";
        $codePhp .= $codePre;
        $codePhp .= '    ?>' . "\n";
        $codePhp .= $codeHtml . "\n";
        $codePhp .= '    <?php' . "\n";
        $codePhp .= '  }' . "\n";
        $codePhp .= '}' . "\n";
        $codePhp .= "\n";

        file_put_contents($path, $codePhp, LOCK_EX);
        chmod($path, 0755);

        return true;
    }

    /**
     * 更新后台模板
     *
     * @param string $app 应用名
     * @param string $template 模析名
     * @param string $theme 主题名
     * @return bool 是否更新成功
     */
    public function updateAdminTemplate($app, $template, $theme)
    {
        return $this->updateTemplate($app, $template, $theme, true);
    }


    /*
     * 保存配置文件到指定咱径
     *
     * @param string $app 应用名称
     * @param string $config 配置文件类
     *
     * @return bool 是否保存成功
     */
    public function updateConfig($app, $config)
    {
        $comments = array();
        if (file_exists($path)) {
            $f = fopen($path, 'r');
            while (!feof($f)) {
                $line = fgets($f, 4096);
                $line = trim($line);
                if (strlen($line) > 8 && strtolower(substr($line, 0, 8)) == 'public $') {
                    $keyStartPos = strpos($line, '$');
                    $keyEndPos = strpos($line, '=');
                    if ($keyStartPos !== false && $keyEndPos !== false) {
                        $key = substr($line, $keyStartPos + 1, $keyEndPos - 1 - $keyStartPos);
                        $key = trim($key);

                        $commentPos = strrpos($line, '//');
                        if ($commentPos !== false) {
                            $comment = substr($line, $commentPos + 2);
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
        $className = substr($class, strrpos($class, '\\') + 1);

        $buf = "<?php\n";
        $buf .= 'namespace ' . $namespace . ';' . "\n\n";
        $buf .= 'class ' . $className . "\n";
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


}
