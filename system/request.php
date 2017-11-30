<?php
namespace system;

/**
 * request
 */
class request
{

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function is_get()
    {
        return 'GET' == $_SERVER['REQUEST_METHOD'] ? true : false;
    }

    public static function is_post()
    {
        return 'POST' == $_SERVER['REQUEST_METHOD'] ? true : false;
    }

    public static function is_ajax()
    {
        return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHTTPREQUEST' == strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])) || !empty($_GET['is_ajax']) || !empty($_POST['is_ajax'])) ? true : false;
    }

    public static function get($name = null, $default = null, $format = 'string')
    {
        return self::_request($_GET, $name, $default, $format);
    }

    public static function post($name = null, $default = null, $format = 'string')
    {
        return self::_request($_POST, $name, $default, $format);
    }

    public static function request($name = null, $default = null, $format = 'string')
    {
        return self::_request($_REQUEST, $name, $default, $format);
    }

    private static function _request($input, $name, $default, $format)
    {
        $magic_quotes_gpc = false;
        if (version_compare(PHP_VERSION, '5.4.0', '<')) $magic_quotes_gpc = get_magic_quotes_gpc() ? true : false;

        $fnFormat = '_format_'.$format;

        if ($name === null) {
            if ($magic_quotes_gpc) $input = self::_stripslashes($input);
            $input = self::$fnFormat($input);
            return $input;
        }

        if (!isset($input[$name])) return $default;

        $value = $input[$name];
        if ($magic_quotes_gpc) $value = self::_stripslashes($value);

        return self::$fnFormat($value);
    }

    private static function _stripslashes($value)
    {
        return is_array($value) ? array_map(array('\system\request', '_stripslashes'), $value) : stripslashes($value);
    }


    private static function _format_int($value)
    {
        return is_array($value) ? array_map(array('\system\request', '_format_int'), $value) : intval($value);
    }

    private static function _format_float($value)
    {
        return is_array($value) ? array_map(array('\system\request', '_format_float'), $value) : floatval($value);
    }

    // 过滤  脚本,样式，框架
    private static function _format_html($value)
    {
        if (is_array($value)) {
            return array_map(array('\system\request', '_format_html'), $value);
        } else {
            $value = preg_replace("@<script(.*?)</script>@is", '', $value);
            $value = preg_replace("@<style(.*?)</style>@is", '', $value);
            $value = preg_replace("@<iframe(.*?)</iframe>@is", '', $value);

            return $value;
        }
    }

    private static function _format_string($value)
    {
        return is_array($value) ? array_map(array('\system\request', '_format_string'), $value) : htmlspecialchars($value);
    }

    private static function _format_($value)
    {
        return $value;
    }

    private static function _format_null($value)
    {
        return $value;
    }

}

