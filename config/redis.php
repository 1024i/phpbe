<?php
namespace config;

class redis
{
    public $host = '127.0.0.1'; // 主机名
    public $port = 6379; // 端口号
    public $timeout = 0;  // 超时时间
    public $persistent = false;  // 是否使用长连接
    public $password = '';  // 密码，不需要时留空
    public $db = 0;  // 默认选中的数据库接
}
