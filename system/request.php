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

        if ($name === null) {
            if ($magic_quotes_gpc) $input = self::_stripslashes($input);

            switch ($format) {
                case 'int':
                    $input = self::_format_int($input);
                    break;
                case 'float':
                    $input = self::_format_float($input);
                    break;
                case 'string':
                    $input = self::_format_string($input);
                    break;
                case 'html':
                    $input = self::_format_html($input);
                    break;
            }
            return $input;
        }

        if (!isset($input[$name])) return $default;

        $value = $input[$name];
        if ($magic_quotes_gpc) $value = self::_stripslashes($value);

        switch ($format) {
            case 'int':
                return self::_format_int($value);

            case 'float':
                return self::_format_float($value);

            case 'string':
                return self::_format_string($value);

            case 'html':
                return self::_format_html($value);
        }

        return $value;
    }

    private static function _stripslashes($string)
    {
        return is_array($string) ? array_map(array('request', '_stripslashes'), $string) : stripslashes($string);
    }

    private static function _format_int($string)
    {
        return is_array($string) ? array_map(array('request', '_format_int'), $string) : intval($string);
    }

    private static function _format_float($string)
    {
        return is_array($string) ? array_map(array('request', '_format_float'), $string) : floatval($string);
    }

    // 过滤  脚本,样式，框架
    private static function _format_html($string)
    {
        if (is_array($string)) {
            return array_map(array('request', '_format_html'), $string);
        } else {
            $string = preg_replace("@<script(.*?)</script>@is", '', $string);
            $string = preg_replace("@<style(.*?)</style>@is", '', $string);
            $string = preg_replace("@<iframe(.*?)</iframe>@is", '', $string);

            return $string;
        }
    }

    private static function _format_string($string)
    {
        return is_array($string) ? array_map(array('request', '_format_string'), $string) : htmlspecialchars($string);
    }

}

