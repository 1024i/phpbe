<?php

namespace system;

/**
 * Session
 */
class Session
{

    // 获取 session id
    public static function getId()
    {
        return session_id();
    }

    // 获取 session name
    public static function getName()
    {
        return session_name();
    }

    // 获取数据库实例
    public static function start()
    {
        $configSession = Be::getConfig('System.Session');

        session_name($configSession->name);

        $driver = $configSession->driver;
        if ($driver != 'File') {
            $className = '\\System\\Session\\Driver\\' . $driver;
            $handler = new $className($configSession);
            session_set_save_handler($handler);
            register_shutdown_function('sessionWriteClose');
        }

        session_start();
    }

    public static function stop()
    {
        session_write_close();
    }

    /**
     * 获取session 值
     *
     * @param string $name 名称
     * @param string $default 默认值
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (isset($_SESSION[$name])) return $_SESSION[$name];
        return $default;
    }

    /**
     * 向session中赋值
     *
     * @param string $name 名称
     * @param string $value 值
     */
    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * 是否已设置指定名称的 session
     *
     * @param string $name 名称
     * @return bool
     */
    public static function has($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     *
     * 删除除指定名称的 session
     * @param string $name 名称
     *
     * @return mixed
     */
    public static function delete($name)
    {
        $value = null;
        if (isset($_SESSION[$name])) {
            $value = $_SESSION[$name];
            unset($_SESSION[$name]);
        }
        return $value;
    }

    /**
     * 销毁 session
     *
     * @return bool
     */
    public static function destroy()
    {
        cookie::delete(session_name());

        session_unset();
        session_destroy();
        return true;
    }

}