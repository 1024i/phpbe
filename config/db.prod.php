<?php
namespace config;

class db
{
    public $master = [
        'driver' => 'mysql',
        'host' => '127.0.0.1', // 主机名
        'port' => 3306, // 端口号
        'user' => 'root', // 用户名
        'pass' => '', // 密码
        'name' => 'phpbe' // 数据库名称
    ]; // 主数据库


    public $host = '127.0.0.1'; // 主机名
    public $port = 3306; // 端口号
    public $user = 'username'; // 用户名
    public $pass = 'password'; // 密码
    public $name = 'phpbe'; // 数据库名称
}
