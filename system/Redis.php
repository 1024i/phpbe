<?php
namespace System;

/**
 * Redis 缓存类
 */
class Redis
{

    private static $instance = null; // 数据库连接

    /**
     * 连接数据库
     *
     * @return bool 是否连接成功
     */
    public static function connect()
    {
        if (self::$instance === null) {
            if (!extension_loaded('Redis')) response::end('服务器未安装 Redis 扩展！');

            $config = Be::getConfig('System.Redis');
            $instance = new \redis();
            $fn = $config->persistent ? 'pconnect' : 'connect';
            if ($config->timeout > 0)
                $instance->$fn($config->host, $config->port, $config->timeout);
            else
                $instance->$fn($config->host, $config->port);
            if ('' != $config->password) $instance->auth($config->password);
            if (0 != $config->db) $instance->select($config->db);

            self::$instance = $instance;
        }
        return true;
    }

    /**
     * 获取 redis 实例
     *
     * @return \redis
     */
    public static function getInstance()
    {
        self::connect();
        return self::$instance;
    }

    /**
     * 封装 redis 方法
     *
     * @param string $fn redis 扩展方法名
     * @param array() $args 传入的参数
     * @return mixed
     */
    public static function __callStatic($fn, $args)
    {
        self::connect();
        return call_user_func_array(array(self::$instance,$fn), $args);
    }

}
