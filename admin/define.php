<?php
define('IS_BACKEND', true); // 是否后台

/*
 * URL_ROOT: 网站网址
 * 可改为实际网址以提高性能， 结尾不带左斜杠("/")
 * 如: define('URL_ROOT', 'http://www.phpbe.com');
 * 非根目录时:
 * define('URL_ROOT', 'http://www.phpbe.com/xxx');
 * define('URL_ROOT', 'http://www.phpbe.com/xxx/xxx');
*/
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$url .= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']));
$url .= substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/Admin/index' . (defined('ENVIRONMENT') ? ('.' . ENVIRONMENT) : '') . '.php'));
define('URL_ROOT', $url);
