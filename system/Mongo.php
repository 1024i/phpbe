<?php

namespace System;

/**
 * MongoDB
 */
class Mongo
{

    private static $connection = null; // mongodb 数据库连接
    private static $db = null; // mongodb 数据库连接
    private static $collection = null; // mongodb 数据库连接

    /**
     * 连接数据库
     *
     * @return bool 是否连接成功
     * @throws \Exception
     */
    public static function connect()
    {
        if (self::$connection === null) {
            if (!extension_loaded('mongoDb')) throw new \Exception('服务器未安装mongoDb扩展！');

            $config = Be::getConfig('mongo');
            $connection = new \MongoClient($config->host . ':' . $config->port);
            self::$collection = $connection;
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
     * 切换数据库
     *
     * @param $db
     */
    public static function setDb($db)
    {
        self::connect();
        $connection = self::$connection;
        self::$db = $connection->$db; // 选择数据库
    }

    /**
     * 切换集合
     *
     * @param $collection
     * @throws \Exception
     */
    public static function setCollection($collection)
    {
        self::connect();

        if (self::$db === null) throw new \Exception('未选择数据库！');

        $db = self::$db;
        self::$collection = $db->$collection; // 选择数据库
    }

    /**
     * 封装 mongoDb 方法
     *
     * @param string $fn mongoDb 扩展方法名
     * @param array() $args 传入的参数
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($fn, $args)
    {
        self::connect();

        if (self::$db === null) throw new \Exception('未选择数据库！');
        if (self::$collection === null) throw new \Exception('未选择集合！');

        return call_user_func_array(array(self::$collection, $fn), $args);
    }
}
