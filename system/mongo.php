<?php
namespace system;

/**
 * MongoDB
 */
class mongodb
{

    private static $connection = null; // mongodb 数据库连接
    private static $error = array(); // 保存错误信息

     /*
     * 连接数据库
     *
     * @return bool 是否连接成功
     */
    public static function connect()
    {
        if (self::$connection === null) {
            if (!extension_loaded('mongoDb')) be_exit('服务器未安装mongoDb扩展！');

            $config = be::get_config('mongo');
            $connection = new \mongoDb();
            $fn = $config->persistent ? 'pconnect' : 'connect';
            if ($config->timeout > 0)
                $connection->$fn($config->host, $config->port, $config->timeout);
            else
                $connection->$fn($config->host, $config->port);
            if ('' != $config->password) $instance->auth($config->password);
            if (0 != $config->db) $instance->select($config->db);


            $config = be::get_config('mongo');
            self::$connection = new \MongoClient($config->host . ':' . $config->port);
            self::$dbname = self::$connection->selectDB($config->dbname);
            self::$db = self::$connection->selectCollection($config->table);

            self::$connection = $connection;
        }
        return true;



    }

    /**
     * 获取 MongoDB 版本号
     *
     * @return string
     */
    public static function get_version()
    {
        return \MongoClient::VERSION;
    }

    public static function clear_error()
    {
        self::$error = null;
    }

    public static function set_error($error)
    {
        self::$error = $error;
    }

    public static function get_error()
    {
        return self::$error;
    }

    public static function has_error()
    {
        return count(self::$error) > 0;
    }


    /**
     * 获取 mongoDb 实例
     *
     * @return \mongoDb
     */
    public static function get_connection()
    {
        self::connect();
        return self::$connection;
    }

    /**
     * 封装 mongoDb 方法
     *
     * @param string $fn mongoDb 扩展方法名
     * @param array() $args 传入的参数
     * @return mixed
     */
    public static function __callStatic($fn, $args)
    {
        self::connect();
        return call_user_func_array(array(self::$connection, $fn), $args);
    }
}
