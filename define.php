<?php
/*
定义BE框架最常用的常量
*/

define('DS', DIRECTORY_SEPARATOR); // DS: 目录分格符 windows 下为右斜杠（C:\windows\），linux下为左斜杠（/var/www/)

define('CACHE', 'cache'); // 缓存 目录名
define('DATA', 'data'); // 可写文件存储 目录名

// define('PATH_ROOT', str_replace('\\', '/', __DIR__));
define('PATH_ROOT', __DIR__); // BE框架的根路径 绝对路径
define('PATH_CACHE', PATH_ROOT . '/' . CACHE); // 缓存 绝对路径
define('PATH_DATA', PATH_ROOT . '/' . DATA); // 可写文件存储 绝对路径

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
    $url .= substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/index' . (defined('ENV') ? ('.' . ENV) : '') . '.php'));
    define('URL_ROOT', $url);
}
define('URL_DATA', URL_ROOT . '/' . DATA); // 可写文件存储 网址
