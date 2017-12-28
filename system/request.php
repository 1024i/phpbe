<?php
namespace system;

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
        return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHTTPREQUEST' == strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])) || !empty($_GET['isAjax']) || !empty($_POST['isAjax'])) ? true : false;
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
        $magicQuotesGpc = false;
        if (version_compare(PHP_VERSION, '5.4.0', '<')) $magicQuotesGpc = get_magic_quotes_gpc() ? true : false;

        $fnFormat = 'Format_'.$format;

        if ($name === null) {
            if ($magicQuotesGpc) $input = self::_stripslashes($input);
            $input = self::$fnFormat($input);
            return $input;
        }

        if (!isset($input[$name])) return $default;

        $value = $input[$name];
        if ($magicQuotesGpc) $value = self::_stripslashes($value);

        return self::$fnFormat($value);
    }

    private static function _stripslashes($value)
    {
        return is_array($value) ? array_map(array('\system\Request', '_stripslashes'), $value) : stripslashes($value);
    }

    private static function formatInt($value)
    {
        return is_array($value) ? array_map(array('\system\Request', 'formatInt'), $value) : intval($value);
    }

    private static function formatFloat($value)
    {
        return is_array($value) ? array_map(array('\system\Request', 'formatFloat'), $value) : floatval($value);
    }

    private static function formatBool($value)
    {
        return is_array($value) ? array_map(array('\system\Request', 'formatBool'), $value) : boolval($value);
    }

    private static function formatString($value)
    {
        return is_array($value) ? array_map(array('\system\Request', 'formatString'), $value) : htmlspecialchars($value);
    }

    // 过滤  脚本,样式，框架
    private static function formatHtml($value)
    {
        if (is_array($value)) {
            return array_map(array('\system\Request', 'formatHtml'), $value);
        } else {
            $value = preg_replace("@<script(.*?)</script>@is", '', $value);
            $value = preg_replace("@<style(.*?)</style>@is", '', $value);
            $value = preg_replace("@<iframe(.*?)</iframe>@is", '', $value);

            return $value;
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

}

