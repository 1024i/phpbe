<?php
/*
定义BE框架最常用的常量

DS: 目录分格符
	windows 下为右斜杠, 如 C:\windnow\system\
	linux 下为左斜杠。 /var/www/

PATH_ROOT：BE框架的根路径
ADMIN: 后台目录名
PATH_ADMIN: 后台绝对路径
*/
define('DS', DIRECTORY_SEPARATOR);

if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
    define('PATH_ROOT', str_replace('\\', '/', __DIR__));
} else {
    define('PATH_ROOT', __DIR__);
}

define('ADMIN', 'admin');
define('PATH_ADMIN', PATH_ROOT . '/' . ADMIN);

/*
 * URL_ROOT: 网站网址
 * 可改为实际网址以提高性能， 结尾不带左斜杠("/")
 * 如: define('URL_ROOT', 'http://www.phpbe.com');
 * 非根目录时:
 * define('URL_ROOT', 'http://www.phpbe.com/xxx');
 * define('URL_ROOT', 'http://www.phpbe.com/xxx/xxx');
 */
if (!defined('URL_ROOT')) { // 后台管理
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
    $url .= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']));
    $url .= substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/index' . (defined('ENVIRONMENT') ? ('.' . ENVIRONMENT) : '') . '.php'));
    define('URL_ROOT', $url);
}

define('URL_ADMIN', URL_ROOT . '/' . ADMIN);

// 可写文件存储路径
define('PATH_CACHE', PATH_ROOT . '/cache');

// 可写文件存储 目录名
define('DATA', 'data');

// 可写文件存储路径
define('PATH_DATA', PATH_ROOT . '/' . DATA);

// 可写文件存储 网址
define('URL_DATA', URL_ROOT . '/' . DATA);
