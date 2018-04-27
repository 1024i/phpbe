<?php
namespace System;

/**
 * Request
 */
class Request
{

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function isGet()
    {
        return 'GET' == $_SERVER['REQUEST_METHOD'] ? true : false;
    }

    public static function isPost()
    {
        return 'POST' == $_SERVER['REQUEST_METHOD'] ? true : false;
    }

    public static function isAjax()
    {
        return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHTTPREQUEST' == strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])) || isset($_GET['isAjax']) || isset($_POST['isAjax'])) ? true : false;
    }

    /**
     * 获取请求者的 IP 地址
     */
    public static function ip() {
        $ip = null;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $pos = strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
            if (false !== $pos) {
                $ip = substr($_SERVER['HTTP_X_FORWARDED_FOR'], 0, $pos);
            } else {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            }

            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * 获取 $_GET 数据
     * @param string|null $name 参数量
     * @param string|null  $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function get($name = null, $default = null, $format = 'string')
    {
        return self::_request($_GET, $name, $default, $format);
    }

    /**
     * 获取 $_POST 数据
     * @param string|null $name 参数量
     * @param string|null  $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function post($name = null, $default = null, $format = 'string')
    {
        return self::_request($_POST, $name, $default, $format);
    }

    /**
     * 获取 $_REQUEST 数据
     * @param string|null $name 参数量
     * @param string|null  $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function request($name = null, $default = null, $format = 'string')
    {
        return self::_request($_REQUEST, $name, $default, $format);
    }

    /**
     * 获取 $_SERVER 数据
     * @param string|null $name 参数量
     * @param string|null  $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function server($name = null, $default = null, $format = 'string')
    {
        return self::_request($_SERVER, $name, $default, $format);
    }

    /**
     * 获取 $_COOKIE 数据
     * @param string|null $name 参数量
     * @param string|null  $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public static function cookie($name = null, $default = null, $format = 'string')
    {
        return self::_request($_COOKIE, $name, $default, $format);
    }

    /**
     * 获取上传的文件
     * @param string|null $name 参数量
     * @return array|null
     */
    public static function files($name = null)
    {
        if ($name === null) {
            return $_FILES;
        }

        if (!isset($_FILES[$name])) return null;

        return $_FILES[$name];
    }

    private static function _request($input, $name, $default, $format)
    {
        if ($name === null) {
            if ($format instanceof \Closure) {
                $input = self::formatByClosure($input, $format);
            } else {
                $fnFormat = 'format'.ucfirst($format);
                $input = self::$fnFormat($input);
            }

            return $input;
        }

        if (!isset($input[$name])) return $default;
        $value = $input[$name];

        if ($format instanceof \Closure) {
            return self::formatByClosure($value, $format);
        } else {
            $fnFormat = 'format'.ucfirst($format);
            return self::$fnFormat($value);
        }
    }

    private static function formatInt($value)
    {
        return is_array($value) ? array_map(array('System\Request', 'formatInt'), $value) : intval($value);
    }

    private static function formatFloat($value)
    {
        return is_array($value) ? array_map(array('System\Request', 'formatFloat'), $value) : floatval($value);
    }

    private static function formatBool($value)
    {
        return is_array($value) ? array_map(array('System\Request', 'formatBool'), $value) : boolval($value);
    }

    private static function formatString($value)
    {
        return is_array($value) ? array_map(array('System\Request', 'formatString'), $value) : htmlspecialchars($value);
    }

    // 过滤  脚本,样式，框架
    private static function formatHtml($value)
    {
        if (is_array($value)) {
            return array_map(array('System\Request', 'formatHtml'), $value);
        } else {
            $value = preg_replace("@<script(.*?)</script>@is", '', $value);
            $value = preg_replace("@<style(.*?)</style>@is", '', $value);
            $value = preg_replace("@<iframe(.*?)</iframe>@is", '', $value);

            return $value;
        }
    }

    /**
     * 格式化 IP
     * @param $value
     * @return array|string
     */
    private static function formatIp($value)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = self::formatIp($v);
            }
            return $returns;
        } else {
            if(filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            } else {
                return 'invalid';
            }
        }
    }

    private static function format($value)
    {
        return $value;
    }

    private static function formatNull($value)
    {
        return $value;
    }

    private static function formatByClosure($value, \Closure $closure)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = self::formatByClosure($v, $closure);
            }
            return $returns;
        } else {
            return $closure($value);
        }

    }

}

