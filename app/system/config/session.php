<?php
namespace App\System\Config;

class Session
{

    public $name = 'SSID'; // 用在 cookie 或者 URL 中的会话名称， 例如：PHPSESSID。 只能使用字母和数字，建议尽可能的短一些

    public $expire = 1440;  // 超时时间

    public $driver = 'Default';  // SESSION 驱动 Default：系统默认/Mysql/Memcache/Memcached/Redis

    // mysql 配置项
    /*
    public $mysql = array(
        'host' => '127.0.0.1', // 主机名
        'port' => '3306', // 端口号
        'user' => 'root', // 用户名
        'pass' => '', // 密码
        'name' => 'beV2', // 数据库名
        'table' => 'session' // 存放session的表名
   );
    */

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
    /*
    public $redis = array(
        'host' => '127.0.0.1', // 主机名
        'port' => 6379, // 端口号
        'timeout' => 0, // 超时时间
        'persistent' => false, // 是否使用长连接
        'password' => '', // 密码，不需要时留空
        'db'=> 0 // 默认选中数据库
   );
    */

}
