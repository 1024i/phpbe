<?php
namespace App\System\Config;

class Admin
{
    public $debug = 1;  // 调试模式 0: 不开启 / 1：开启
    public $apps = array('article', 'adminUser', 'user', 'system');
    public $limit = 12;  // 默认分页显示条数
    public $theme = 'classic';  // 默认主题
}
