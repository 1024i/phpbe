<?php

namespace system;


/**
 *  BE系统资源工厂
 * @package system
 *
 * @method mixed get_lib(string $lib) static 获取指定的类库
 * @method mixed get_ui(string $ui)  static 获取指定的UI
 * @method mixed get_admin_ui(string $ui)  static 获取指定的UI
 * @method controller get_controller(string $controller)  static 获取控制器
 * @method controller get_admin_controller(string $controller)  static 获取后台控制器
 * @method mixed get_service(string $service)  static 获取服务
 * @method mixed get_admin_service(string $service)  static 获取后台服务
 *
 */
abstract class be
{

    private static $cache = array(); // 缓存资源实例

    private static $version = '2.0'; // 系统版本号


    /*
     * 封装 获取资源方法
     */
    public static function __callStatic($fn, $args)
    {
        if (substr($fn, 0, 4) == 'get_' && count($args) == 1) {
            $instance = substr($fn, 4);
            $key = $instance . '-' . $args[0];
            if (isset(self::$cache[$key])) return self::$cache[$key];

            if (in_array($instance, array('lib', 'ui', 'admin_ui'))) {
                $class_name = '\\' . str_replace('_', '\\', $instance) . '\\' . $args[0] . '\\' . $args[0];
            } else {
                $class_name = '\\' . str_replace('_', '\\', $instance) . '\\' . $args[0];
            }
            self::$cache[$key] = new $class_name();
            return self::$cache[$key];
        }

        return null;
    }

    /**
     * 获取数据库连接对象
     *
     * @param string $config 配置文件名
     * @return \object
     * @throws \Exception
     */
    public static function get_db($db = 'master')
    {
        $key = 'db-' . $db;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $config = be::get_config('db');
        if (!isset($config->$db)) {
            throw new \exception('数据库配置项（'.$db.'）不存在！');
        }

        $config = $config->$db;
        switch ($config['driver']) {
            case 'mysql':
                self::$cache[$key] = new \system\db\driver\mysql($config);
                break;
            default:
                throw new \exception('数据库配置项（'.$db.'）指定的数据库驱动'.$config['driver'].'不支持！');
        }

        return self::$cache[$key];
    }

    /**
     * 获取指定的配置文件
     *
     * @param string $config 配置文件名
     * @return \object
     */
    public static function get_config($config)
    {
        $class = '\\data\\config\\' . $config;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (defined('ENVIRONMENT')) {
            $path = PATH_DATA . DS . 'config' . DS . $config . '.' . ENVIRONMENT . '.php';
            if (file_exists($path)) {
                include_once $path;
            }
        }

        $instance = new $class();
        self::$cache[$class] = $instance;
        return self::$cache[$class];
    }

    /**
     * 获取指定的后台配置文件
     *
     * @param string $config 配置文件名
     * @return \object
     */
    public static function get_admin_config($config)
    {
        $class = '\\data\\admin_config\\' . $config;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (defined('ENVIRONMENT')) {
            $path = PATH_DATA . DS . 'admin_config' . DS . $config . '.' . ENVIRONMENT . '.php';
            if (file_exists($path)) {
                include_once $path;
            }
        }

        $instance = new $class();
        self::$cache[$class] = $instance;
        return self::$cache[$class];
    }

    /**
     * 获取一个应用
     *
     * @param string $app 应用名
     * @return app
     */
    public static function get_app($app)
    {
        $class = '\\app\\' . $app;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $instance = new $class();
        $instance->set_name($app);
        self::$cache[$class] = $instance;
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个数据库行记灵对象
     *
     * @param string $row 据库行记灵对象名
     * @return row | mixed
     * @throws \Exception
     */
    public static function get_row($row)
    {
        $path = PATH_ROOT . DS . 'row' . DS . $row . '.php';
        if (file_exists($path)) {
            $class_name = '\\row\\' . $row;
            return (new $class_name());
        }

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'row' . DS . $row . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_row($row);
            if (!file_exists($path)) {
                throw new \exception('row '.$row.' 不存在！');
            }
        }
        $class_name = '\\data\\system\\cache\\row\\' . $row;
        return (new $class_name());
    }


    /**
     * 获取指定的一个数据库表对象
     *
     * @param string $table 表名
     * @return table
     * @throws \Exception
     */
    public static function get_table($table = null)
    {
        if ($table === null) return new table();

        $path = PATH_ROOT . DS . 'table' . DS . $table . '.php';
        if (file_exists($path)) {
            $class_name = '\\table\\' . $table;
            return (new $class_name());
        }

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'table' . DS . $table . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_table($table);
            if (!file_exists($path)) {
                throw new \exception('table '.$table.' 不存在！');
            }
        }
        $class_name = '\\data\\system\\cache\\table\\' . $table;
        return (new $class_name());
    }

    /**
     * 获取指定的一个菜单
     *
     * @param string $menu 菜单名
     * @return menu
     * @throws \Exception
     */
    public static function get_menu($menu)
    {
        $class = '\\data\\system\\cache\\menu\\' . $menu;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'menu' . DS . $menu . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_menu($menu);
            if (!file_exists($path)) {
                throw new \exception('菜单 '.$menu.' 不存在！');
            }
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个用户角色信息
     *
     * @param int $role_id 角色ID
     * @return object
     * @throws \Exception
     */
    public static function get_user_role($role_id)
    {
        $class = '\\data\\system\\cache\\user_role\\user_role_' . $role_id;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'user_role' . DS . 'user_role_'. $role_id . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_user_role($role_id);
            if (!file_exists($path)) {
                throw new \exception('前台用户角色 #'.$role_id.' 不存在！');
            }
        }
        include_once $path;

        if (!class_exists($class)) return null;

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个用户角色信息
     *
     * @param int $role_id 角色ID
     * @return object
     * @throws \Exception
     */
    public static function get_admin_user_role($role_id)
    {
        $class = '\\data\\system\\cache\\admin_user_role\\admin_user_role_' . $role_id;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'admin_user_role' . DS . 'admin_user_role_' . $role_id . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_admin_user_role($role_id);
            if (!file_exists($path)) {
                throw new \exception('后台管理员角色 #'.$role_id.' 不存在！');
            }
        }
        include_once $path;

        if (!class_exists($class)) return null;

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个自定义内容
     *
     * @param string $class 类名
     * @return string
     */
    public static function get_html($class)
    {
        $key = 'html-' . $class;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html' . DS . $class . '.html';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_html($class);
            if (!file_exists($path)) return '';
        }
        self::$cache[$key] = file_get_contents($path);
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个模板
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return template
     * @throws \Exception
     */
    public static function get_template($template, $theme = null)
    {
        $config = be::get_config('system');
        if ($theme === null) {
            $theme = $config->theme;
        }

        $key = 'template-' . $theme . '-' . $template;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'template' . DS . $theme . DS . str_replace('.', DS, $template) . '.php';
        if ($config->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_template($theme, $template);
            if (!file_exists($path)) {
                throw new \exception('模板（'.$template.'）不存在！');
            }
        }

        $template_class_name = '\\data\\system\\cache\\template\\' . $theme . '\\' . str_replace('.', '\\', $template);
        $template_instance = new $template_class_name();

        self::$cache[$key] = $template_instance;
        return self::$cache[$key];
    }


    /**
     * 获取指定的一个后台模板
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return template
     * @throws \Exception
     */
    public static function get_admin_template($template, $theme = null)
    {
        $config = be::get_admin_config('system');
        if ($theme === null) {
            $theme = $config->theme;
        }

        $key = 'admin_template-' . $theme . '-' . $template;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'admin_template' . DS . $theme . DS . str_replace('.', DS, $template) . '.php';
        if ($config->debug || !file_exists($path)) {
            $service_system = be::get_service('system');
            $service_system->update_cache_admin_template($theme, $template);
            if (!file_exists($path)) {
                throw new \exception('后台模板（'.$template.'）不存在！');
            };
        }

        $template_class_name = '\\data\\system\\cache\\admin_template\\' . $theme . '\\' . str_replace('.', '\\', $template);
        $template_instance = new $template_class_name();

        self::$cache[$key] = $template_instance;
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个路由
     *
     * @param string $router 路由名
     * @return router
     */
    public static function get_router($router)
    {
        $key = 'router-' . $router;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = PATH_ROOT . DS . 'router' . DS . $router . '.php';
        if (file_exists($path)) {
            $class_name = '\\router\\' . $router;
            self::$cache[$key] = new $class_name();
        } else {
            self::$cache[$key] = new router();
        }
        return self::$cache[$key];
    }


    /**
     * 获取一个用户 实例
     *
     * @param int $id 用户编号
     * @return \stdClass
     */
    public static function get_user($id = 0)
    {
        $key = 'user-' . $id;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $user = null;
        if ($id == 0) {
            $user = session::get('_user');
        } else {
            $db = be::get_db();
            $user = $db->get_object('SELECT * FROM be_user WHERE id=' . intval($id));
            if ($user) {
                unset($user->password);
                unset($user->token);
            }
        }

        if (!$user) {
            // 游客或不存在的用户(id == 0)
            $user = new \stdClass();
            $user->id = 0;
            $user->username = '';
            $user->name = '';
            $user->role_id = 1;
            return $user;
        }

        self::$cache[$key] = $user;
        return self::$cache[$key];
    }

    /**
     * 获取后台管理员用户 实例
     *
     * @param int $id 用户编号
     * @return \stdClass
     */
    public static function get_admin_user($id = 0)
    {
        $key = 'admin_user-' . $id;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $user = null;
        if ($id == 0) {
            $user = session::get('_admin_user');
        } else {
            $db = be::get_db();
            $user = $db->get_object('SELECT * FROM be_admin_user WHERE id=' . intval($id));
            if ($user != null) {
                unset($user->password);
                unset($user->token);
            }
        }

        if ($user === null) {
            // 游客或不存在的用户(id == 0)
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '';
            return $user;
        }

        self::$cache[$key] = $user;
        return self::$cache[$key];
    }

    /**
     * 获取系统版本号
     *
     * @return string
     */
    public static function get_version()
    {
        return self::$version;
    }

}
