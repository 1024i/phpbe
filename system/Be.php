<?php
namespace System;


/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be
{

    private static $cache = array(); // 缓存资源实例

    private static $version = '2.0'; // 系统版本号


    /**
     * 获取数据库对象
     *
     * @param string $db 数据库名
     * @return \System\Db\Driver
     * @throws \Exception
     */
    public static function getDb($db = 'master')
    {
        $key = 'db:' . $db;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $config = Be::getConfig('System.Db');
        if (!isset($config->$db)) {
            throw new \Exception('数据库配置项（' . $db . '）不存在！');
        }

        $config = $config->$db;

        $class = 'System\\Db\\Driver\\' . $config['driver'] . 'Impl';
        if (!class_exists($class)) throw new \Exception('数据库配置项（' . $db . '）指定的数据库驱动' . $config['driver'] . '不支持！');

        self::$cache[$key] = new $class($config);
        return self::$cache[$key];
    }

    /**
     * 获取指定的UI
     *
     * @param string $ui UI名，可指定命名空间，调用第三方UI扩展
     * @return Ui | mixed
     * @throws \Exception
     */
    public static function getUi($ui)
    {
        $class = null;
        if (strpos($ui, '\\') === false) {
            $class = 'Phpbe\\Ui\\' . $ui . '\\' . $ui;
        } else {
            $class = $ui;
        }

        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new \Exception('UI ' . $class . ' 不存在！');

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的库
     *
     * @param string $lib 库名，可指定命名空间，调用第三方库
     * @return Lib | mixed
     * @throws \Exception
     */
    public static function getLib($lib)
    {
        $class = null;
        if (strpos($lib, '\\') === false) {
            $class = 'Phpbe\\Lib\\' . $lib . '\\' . $lib;
        } else {
            $class = $lib;
        }

        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new \Exception('库 ' . $class . ' 不存在！');

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的配置文件
     *
     * @param string $config 配置文件名
     * @return \Object
     * @throws \Exception
     */
    public static function getConfig($config)
    {
        $key = 'config:' . $config;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $pos = strpos($config, '.');
        if ($pos === false) throw new \Exception('配置文件参数 ' . $config . ' 无效！');

        $app = substr($config, 0, $pos);
        $configSuffix = substr($config, $pos + 1);

        $class = 'App\\' . $app . '\\Config\\' . $configSuffix;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();;
            return self::$cache[$key];
        }

        // 缓存类的配置文件
        $path = PATH_CACHE . '/Config/' .  $app  . '/' . $configSuffix . '.php';
        if (Be::getConfig('System.System')->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateConfig($app, $configSuffix);
        }

        $class = 'Cache\\Config\\' . $app . '\\' . $configSuffix;
        if (!class_exists($class)) throw new \Exception('配置文件 ' . $config . ' 不存在！');

        self::$cache[$key] = new $class();;
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个服务
     *
     * @param string $service 服务名
     * @return Service | mixed
     * @throws \Exception
     */
    public static function getService($service)
    {
        $key = 'service:' . $service;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $pos = strpos($service, '.');
        if ($pos === false) throw new \Exception('服务参数 ' . $service . ' 无效！');

        $app = substr($service, 0, $pos);
        $serviceSuffix = substr($service, $pos + 1);

        $class = 'App\\' . $app . '\\Service\\' . $serviceSuffix;

        if (!class_exists($class)) throw new \Exception('服务 ' . $service . ' 不存在！');

        self::$cache[$key] = new $class();
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个数据库行记灵对象
     *
     * @param string $row 数据库行记灵对象名
     * @return Row | mixed
     * @throws \Exception
     */
    public static function getRow($row)
    {
        $pos = strpos($row, '.');
        if ($pos === false) throw new \Exception('行记灵对象 ' . $row . ' 无效！');

        $app = substr($row, 0, $pos);
        $rowSuffix = substr($row, $pos + 1);

        $class = 'App\\' . $app . '\\Row\\' . $rowSuffix;
        if (class_exists($class)) return (new $class());

        $path = PATH_CACHE . '/Row/' .  $app . '/' . $rowSuffix . '.php';
        if (Be::getConfig('System.System')->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateRow($app, $rowSuffix);
        }

        $class = 'Cache\\Row\\' . $app . '\\' . $rowSuffix;
        if (!class_exists($class)) {
            throw new \Exception('行记灵对象 ' . $row . ' 不存在！');
        }

        return (new $class());
    }

    /**
     * 获取指定的一个数据库表对象
     *
     * @param string $table 表名
     * @return Table
     * @throws \Exception
     */
    public static function getTable($table = null)
    {
        if ($table === null) return new Table();

        $pos = strpos($table, '.');
        if ($pos === false) throw new \Exception('表对象 ' . $table . ' 无效！');

        $app = substr($table, 0, $pos);
        $tableSuffix = substr($table, $pos + 1);

        $class = 'App\\' . $app . '\\Table\\' . $tableSuffix;
        if (class_exists($class)) return (new $class());

        $path = PATH_CACHE . '/Table/' .  $app . '/' . $tableSuffix . '.php';
        if (Be::getConfig('System.System')->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateTable($app, $tableSuffix);
        }

        $class = 'Cache\\Table\\' . $app . '\\' . $tableSuffix;
        if (!class_exists($class)) {
            throw new \Exception('表对象 ' . $table . ' 不存在！');
        }

        return (new $class());
    }

    /**
     * 获取指定的一个自定义内容
     *
     * @param string $class 类名
     * @return string
     */
    public static function getHtml($class)
    {
        $key = 'html-' . $class;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = PATH_CACHE . '/Html/' .  $class . '.html';
        if (Be::getConfig('System.System')->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateHtml($class);
        }

        $html = '';
        if (file_exists($path)) {
            $html = file_get_contents($path);
        }

        self::$cache[$key] = $html;
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个菜单
     *
     * @param string $menu 菜单名
     * @return Menu
     * @throws \Exception
     */
    public static function getMenu($menu)
    {
        $class = 'Cache\\Menu\\' . $menu;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_CACHE . '/Menu/' .  $menu . '.php';
        if (Be::getConfig('System.System')->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateMenu($menu);
        }

        if (!class_exists($class)) {
            throw new \Exception('菜单 ' . $menu . ' 不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个用户角色信息
     *
     * @param int $roleId 角色ID
     * @return Role
     * @throws \Exception
     */
    public static function getUserRole($roleId)
    {
        $class = 'Cache\\UserRole\\UserRole' . $roleId;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_CACHE . '/UserRole/UserRole' . $roleId . '.php';
        if (Be::getConfig('System.System')->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateUserRole($roleId);
        }

        if (!class_exists($class)) {
            throw new \Exception('前台用户角色 #' . $roleId . ' 不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个用户角色信息
     *
     * @param int $roleId 角色ID
     * @return Role
     * @throws \Exception
     */
    public static function getAdminUserRole($roleId)
    {
        $class = 'Cache\\AdminUserRole\\AdminUserRole' . $roleId;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_CACHE . '/AdminUserRole/AdminUserRole' . $roleId . '.php';
        if (Be::getConfig('System.System')->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateAdminUserRole($roleId);
        }

        if (!class_exists($class)) {
            throw new \Exception('后台管理员角色 #' . $roleId . ' 不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取一个应用
     *
     * @param string $app 应用名
     * @return App
     * @throws \Exception
     */
    public static function getApp($app)
    {
        $class = 'App\\' . $app;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new \Exception('应用 ' . $app . ' 不存在！');

        $instance = new $class();
        self::$cache[$class] = $instance;
        return self::$cache[$class];
    }

    /**
     * 获取指定的控制器
     *
     * @param string $app 应用名
     * @param string $controller 控制器名
     * @return Controller
     * @throws \Exception
     */
    public static function getController($app, $controller)
    {
        $class = 'App\\' . $app . '\\Controller\\' . $controller;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new \Exception('控制器 ' . $app . '.' . $controller . ' 不存在！');

        self::$cache[$class] = new $class();;
        return self::$cache[$class];
    }

    /**
     * 获取指定的后台控制器
     *
     * @param string $app 应用名
     * @param string $adminController 后台控制器名
     * @return AdminController
     * @throws \Exception
     */
    public static function getAdminController($app, $adminController)
    {
        $class = 'App\\' . $app . '\\AdminController\\' . $adminController;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new \Exception('后台控制器 ' . $app . '.' . $adminController . ' 不存在！');

        self::$cache[$class] = new $class();;
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个模板
     *
     * @param string $app 应用名
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return Template
     * @throws \Exception
     */
    public static function getTemplate($app, $template, $theme = null)
    {
        $config = Be::getConfig('System.System');
        if ($theme === null) $theme = $config->theme;

        $class = 'Cache\\Template\\' . $theme . '\\'  . $app . '\\' . str_replace('.', '\\', $template);
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_CACHE . '/Template/' .  $theme . '/' .  $app . '/' . str_replace('.', '/', $template) . '.php';
        if ($config->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateTemplate($app, $template, $theme);
        }

        if (!class_exists($class)) throw new \Exception('模板（' . $template . '）不存在！');

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个模板
     *
     * @param string $app 应用名
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return Template
     * @throws \Exception
     */
    public static function getAdminTemplate($app, $template, $theme = null)
    {
        $config = Be::getConfig('System.Admin');
        if ($theme === null) $theme = $config->theme;

        $class = 'Cache\\AdminTemplate\\' . $theme . '\\' . $app . '\\' . str_replace('.', '\\', $template);
        if (isset(self::$cache[$class])) return self::$cache[$class];

        $path = PATH_CACHE . '/AdminTemplate/' .  $theme . '/' .  $app . '/' . str_replace('.', '/', $template) . '.php';
        if ($config->debug || !file_exists($path)) {
            $serviceSystem = Be::getService('System.Cache');
            $serviceSystem->updateAdminTemplate($app, $template, $theme);
        }

        if (!class_exists($class)) {
            throw new \Exception('后台模板（' . $template . '）不存在！');
        }

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的一个路由
     *
     * @param string $app 应用名
     * @param string $router 路由名
     * @return Router
     * @throws \Exception
     */
    public static function getRouter($app, $router)
    {
        $key = 'router:' . $app . ':' . $router;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = PATH_ROOT . '/App/' .  $app . '/Router/' .  $router . '.php';
        if (file_exists($path)) {
            $className = '\\App\\' . $app . '\\Router\\' . $router;
            self::$cache[$key] = new $className();
        } else {
            self::$cache[$key] = new router();
        }
        return self::$cache[$key];
    }

    /**
     * 获取指定的一个后台路由
     *
     * @param string $app 应用名
     * @param string $router 路由名
     * @return AdminRouter
     * @throws \Exception
     */
    public static function getAdminRouter($app, $router)
    {
        $key = 'adminRouter:' . $app . ':' . $router;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $path = PATH_ROOT . '/App/' .  $app . '/AdminRouter/' .  $router . '.php';
        if (file_exists($path)) {
            $className = '\\App\\' . $app . '\\AdminRouter\\' . $router;
            self::$cache[$key] = new $className();
        } else {
            self::$cache[$key] = new adminRouter();
        }
        return self::$cache[$key];
    }

    /**
     * 获取一个用户 实例
     *
     * @param int $id 用户编号
     * @return \stdClass
     */
    public static function getUser($id = 0)
    {
        $key = 'user:' . $id;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $user = null;
        if ($id == 0) {
            $user = Session::get('_user');
        } else {
            $user = Be::getTable('System.User')->getObject(intval($id));
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
            $user->roleId = 1;
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
    public static function getAdminUser($id = 0)
    {
        $key = 'adminUser:' . $id;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $user = null;
        if ($id == 0) {
            $user = Session::get('_admin_user');
        } else {
            $user = Be::getTable('System.admin_user')->getObject(intval($id));
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
    public static function getVersion()
    {
        return self::$version;
    }

}
