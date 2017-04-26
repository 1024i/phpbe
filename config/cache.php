<?php
namespace config;

class cache
{
    public $driver = 'redis';  // 缓存类型 file：文件/memcache/memcached/redis


    // memcache 配置项，二维数组，可存放多个服务器配置
    /*
    public $memcache = array(
        array(
            'host' => '127.0.0.1', // 主机名
            'port' => '11211', // 端口号
            'timeout' => 0, // 超时时间
            'persistent' => false, // 是否使用长连接
            'weight' => 1 // 权重
       )
   );
    */


    // memcached 配置项，二维数组，可存放多个服务器配置
    /*
    public $memcached = array(
        array(
            'host' => '127.0.0.1', // 主机名
            'port' => '11211', // 端口号
            'weight' => 1 // 权重
       )
   );
    */


    // REDIS 设置项，未设置时使用系统 REDIS 设置
    public $redis = array(
        'host' => '127.0.0.1', // 主机名
        'port' => 6379, // 端口号
        'timeout' => 10, // 超时时间
        'persistent' => false, // 是否使用长连接
        'password' => '', // 密码，不需要时留空
        'db'=> 0 // 默认选中数据库
   );

}
