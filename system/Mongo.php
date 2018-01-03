<?php
namespace system;

/**
 * MongoDB
 */
class Mongo
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
            if (!extension_loaded('mongoDb')) Response::end('服务器未安装mongoDb扩展！');

            $config = Be::getConfig('mongo');
            $connection = new \MongoDb();
            $fn = $config->persistent ? 'pconnect' : 'connect';
            if ($config->timeout > 0)
                $connection->$fn($config->host, $config->port, $config->timeout);
            else
                $connection->$fn($config->host, $config->port);
            if ('' != $config->password) $instance->auth($config->password);
            if (0 != $config->db) $instance->select($config->db);


            $config = Be::getConfig('mongo');
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
    public static function getVersion()
    {
        return \MongoClient::VERSION;
    }

    public static function clearError()
    {
        self::$error = null;
    }

    public static function setError($error)
    {
        self::$error = $error;
    }

    public static function getError()
    {
        return self::$error;
    }

    public static function hasError()
    {
        return count(self::$error) > 0;
    }


    /**
     * 获取 mongoDb 实例
     *
     * @return \mongoDb
     */
    public static function getConnection()
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
    public static function _CallStatic($fn, $args)
    {
        self::connect();
        return call_user_func_array(array(self::$connection, $fn), $args);
    }
}
