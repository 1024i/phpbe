<?php

namespace system;


/**
 *  BE系统资源工厂
 * @package system
 *
 * @method mixed get_lib(string $lib) static 获取指定的类库
 * @method mixed get_ui(string $ui)  static 获取指定的UI
 * @method controller get_controller(string $controller)  static 获取控制器
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

            if (in_array($instance, array('lib', 'ui'))) {
                $class = '\\' . str_replace('_', '\\', $instance) . '\\' . $args[0] . '\\' . $args[0];
            } else {
                $class = '\\' . str_replace('_', '\\', $instance) . '\\' . $args[0];
            }

            if (!class_exists($class)) throw new \exception('资源' . $class . '不存在！');

            self::$cache[$key] = new $class();
            return self::$cache[$key];
        }

        return null;
    }

    /**
     * 获取数据库对象
     *
     * @param string $db 数据库名
     * @return \system\db\driver
     * @throws \exception
     */
    public static function get_db($db = 'master')
    {
        $key = 'db-' . $db;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $config = be::get_config('db');
        if (!isset($config->$db)) {
            throw new \exception('数据库配置项（' . $db . '）不存在！');
        }

        $config = $config->$db;

        $class = '\\system\\db\\driver\\' . $config['driver'];
        if (!class_exists($class)) throw new \exception('数据库配置项（' . $db . '）指定的数据库驱动' . $config['driver'] . '不支持！');

        self::$cache[$key] = new $class($config);
        return self::$cache[$key];
    }

    /**
     * 获取指定的配置文件
     *
     * @param string $config 配置文件名
     * @return \object
     * @throws \exception
     */
    public static function get_config($config)
    {
        $key = 'config:' . $config;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $pos = strpos($config, '.');
        if ($pos === false) throw new \exception('配置文件参数 ' . $config . ' 无效！');

        $app = substr($config, 0, $pos);
        $config_suffix = substr($config, $pos + 1);

        if (defined('ENVIRONMENT')) {
            $path = PATH_ROOT . DS . 'app' . DS . $app . DS . 'config' . DS . $config_suffix . '.' . ENVIRONMENT . '.php';
            if (file_exists($path)) include_once $path;
        }

        $class = '\\app\\' . $app . '\\config\\' . $config_suffix;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();;
            return self::$cache[$key];
        }

        // 缓存类的配置文件
        $path = PATH_DATA . DS . 'cache' . DS . 'config' . DS . $app . DS . $config_suffix . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_config($app, $config_suffix);
        }

        $class = '\\data\\cache\\config\\' . $app . '\\' . $config_suffix;
        if (!class_exists($class)) throw new \exception('配置文件 ' . $config . ' 不存在！');

        self::$cache[$key] = new $class();;
        return self::$cache[$key];
    }

    /**
     * 获取一个应用
     *
     * @param string $app 应用名
     * @return app
     * @throws \exception
     */
    public static function get_app($app)
    {
        $class = '\\app\\' . $app;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new \exception('应用 ' . $app . ' 不存在！');

        $instance = new $class();
        self::$cache[$class] = $instance;
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个服务
     *
     * @param string $service 服务名
     * @return service | mixed
     * @throws \exception
     */
    public static function get_service($service)
    {
        $key = 'service:' . $service;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $pos = strpos($service, '.');
        if ($pos === false) throw new \exception('服务参数 ' . $service . ' 无效！');

        $app = substr($service, 0, $pos);
        $service_suffix = substr($service, $pos + 1);

        $class = '\\app\\' . $app . '\\service\\' . $service_suffix;

        if (!class_exists($class)) throw new \exception('服务 ' . $service . ' 不存在！');

        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个数据库行记灵对象
     *
     * @param string $row 数据库行记灵对象名
     * @return row | mixed
     * @throws \exception
     */
    public static function get_row($row)
    {
        $pos = strpos($row, '.');
        if ($pos === false) throw new \exception('行记灵对象 ' . $row . ' 无效！');

        $app = substr($row, 0, $pos);
        $row_suffix = substr($row, $pos + 1);

        $path = PATH_ROOT . DS . 'app' . DS . $app . DS . 'row' . DS . $row_suffix . '.php';
        if (file_exists($path)) {
            $class = '\\app\\' . $app . '\\row\\' . $row_suffix;
            if (class_exists($class)) return (new $class());
        }

        $path = PATH_DATA . DS . 'cache' . DS . 'row' . DS . $app . DS . $row_suffix . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_row($app, $row_suffix);
        }

        $class = '\\data\\system\\cache\\row\\' . $app . '\\' . $row_suffix;
        if (!class_exists($class)) {
            throw new \exception('行记灵对象 ' . $row . ' 不存在！');
        }

        return (new $class());
    }


    /**
     * 获取指定的一个数据库表对象
     *
     * @param string $table 表名
     * @return table
     * @throws \exception
     */
    public static function get_table($table = null)
    {
        if ($table === null) return new table();

        $pos = strpos($table, '.');
        if ($pos === false) throw new \exception('表对象 ' . $table . ' 无效！');

        $app = substr($table, 0, $pos);
        $table_suffix = substr($table, $pos + 1);

        $path = PATH_ROOT . DS . 'app' . DS . $app . DS . 'table' . DS . $table_suffix . '.php';
        if (file_exists($path)) {
            $class = '\\app\\' . $app . '\\table\\' . $table_suffix;
            return (new $class());
        }

        $path = PATH_DATA . DS . 'cache' . DS . 'table' . DS . $app . DS . $table_suffix . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_table($app, $table_suffix);
        }

        $class = '\\data\\system\\cache\\table\\' . $app . '\\' . $table_suffix;
        if (!class_exists($class)) {
            throw new \exception('表对象 ' . $table . ' 不存在！');
        }

        return (new $class());
    }

    /**
     * 获取指定的一个菜单
     *
     * @param string $menu 菜单名
     * @return menu
     * @throws \exception
     */
    public static function get_menu($menu)
    {
        $class = '\\data\\system\\cache\\menu\\' . $menu;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_DATA . DS . 'cache' . DS . 'menu' . DS . $menu . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_menu($menu);
        }

        if (!class_exists($class)) {
            throw new \exception('菜单 ' . $menu . ' 不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个用户角色信息
     *
     * @param int $role_id 角色ID
     * @return object
     * @throws \exception
     */
    public static function get_user_role($role_id)
    {
        $class = '\\data\\system\\cache\\user_role\\user_role_' . $role_id;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_DATA . DS . 'cache' . DS . 'user_role' . DS . 'user_role_' . $role_id . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_user_role($role_id);
        }

        if (!class_exists($class)) {
            throw new \exception('前台用户角色 #' . $role_id . ' 不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个用户角色信息
     *
     * @param int $role_id 角色ID
     * @return object
     * @throws \exception
     */
    public static function get_admin_user_role($role_id)
    {
        $class = '\\data\\system\\cache\\admin_user_role\\admin_user_role_' . $role_id;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_DATA . DS . 'cache' . DS . 'admin_user_role' . DS . 'admin_user_role_' . $role_id . '.php';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_admin_user_role($role_id);
        }

        if (!class_exists($class)) {
            throw new \exception('后台管理员角色 #' . $role_id . ' 不存在！');
        }

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

        $path = PATH_DATA . DS . 'cache' . DS . 'html' . DS . $class . '.html';
        if (be::get_config('system')->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_html($class);
        }

        $html = '';
        if (file_exists($path)) {
            $html = file_get_contents($path);
        }

        self::$cache[$key] = $html;
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个模板
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return template
     * @throws \exception
     */
    public static function get_template($template)
    {
        $class = '\\data\\system\\cache\\template\\' . str_replace('.', '\\', $template);
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $config = be::get_config('system.system');

        $path = PATH_DATA . DS . 'cache' . DS . 'template' . DS . str_replace('.', DS, $template) . '.php';
        if ($config->debug || !file_exists($path)) {
            $service_system = be::get_service('system.cache');
            $service_system->update_template($template);
        }

        if (!class_exists($class)) {
            throw new \exception('模板（' . $template . '）不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个路由
     *
     * @param string $router 路由名
     * @return router
     * @throws \exception
     */
    public static function get_router($router)
    {
        $key = 'router-' . $router;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $pos = strpos($router, '.');
        if ($pos === false) throw new \exception('router 参数 ' . $router . ' 无效！');

        $app = substr($router, 0, $pos);
        $router_suffix = substr($router, $pos + 1);

        $path = PATH_ROOT . DS . 'app' . DS . $app . DS . 'router' . DS . $router_suffix . '.php';
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
