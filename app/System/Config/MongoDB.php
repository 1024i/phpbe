<?php
namespace App\System\Config;

class MongoDB
{

    public $master = [
        'host' => '172.24.0.120', // 主机名
        'port' => 27017, // 端口号
        'db' => '' // 数据库
    ];

    public function __construct()
    {
        if (ENV == 'prod') {
            $this->master = [
                'host' => '172.24.0.120', // 主机名
                'port' => 27017, // 端口号
                'db' => '' // 数据库
            ];
        }
    }

}
