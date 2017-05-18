<?php
namespace admin\config;

class system
{
    public $debug = '1';  // 调试模式 0: 不开启 / 1：开启
    public $apps = array('article', 'user', 'system');
    public $limit = '15';  // 默认分页显示条数
    public $theme = 'huxiu';  // 默认主题
}
