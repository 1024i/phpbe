<?php
namespace app\system\config;

class db
{
    public $master = [
        'driver' => 'mysql',
        'host' => '172.24.0.100', // 主机名
        'port' => 3306, // 端口号
        'user' => 'root', // 用户名
        'pass' => '10241024', // 密码
        'name' => 'phpbe' // 数据库名称
    ]; // 主数据库

}
