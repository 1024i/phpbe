<?php

namespace system;

/**
 * response
 * @package system
 *
 * @method void set_title(string $title) static 设置 title
 * @method void set_meta_keywords(string $meta_keywords)  static 设置 meta keywords
 * @method void set_meta_description(string $meta_description)  static 设置 meta description
 */
class response
{
    private static $data = array(); // 暂存数据


    /**
     * 向客户机添加一个字符串值属性的响应头信息
     */
    public static function add_header($name, $value)
    {
    }

    /**
     * 向客户机设置一个字符串值属性的响应头信息，已存在时覆盖
     */
    public static function set_header($name, $value)
    {
    }

    /**
     * 判断是否含响应头信息
     */
    public static function has_header($name)
    {
    }


    /**
     * 设置响应码，比如：200,304,404等
     */
    public static function set_status($status)
    {
    }

    /**
     * 设置设置响应头content-type的内容
     */
    public static function set_content_type($content_type)
    {
        header('Content-type: ' . $content_type);
    }

    /**
     * 请求重定向
     *
     * @param string $url 跳转网址
     */
    public static function redirect($url)
    {
        header('location:' . $url);
        exit();
    }

    /**
     * 设置暂存数据
     * @param string $name 名称
     * @param mixed $value 值 (可以是数组或对象)
     */
    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }

    /**
     * 设置消息
     *
     * @param string $message 消息内容
     * @param string $type 消息类型
     */
    public static function set_message($message, $type = 'success')
    {
        $data = new \stdClass();
        $data->type = $type;
        $data->body = $message;
        session::set('_message', $data);
    }

    /**
     * 获取暂存数据
     *
     * @param string $name 名称
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (isset(self::$data[$name])) return self::$data[$name];
        return $default;
    }

    /**
     * 以 JSON 输出暂存数据
     */
    public static function ajax()
    {
        header('Content-type: application/json');
        echo json_encode(self::$data);
        exit();
    }

    /**
     * 成功
     *
     * @param string $message 消息
     * @param string $redirect_url 跳转网址
     * @param int $code 错误码
     */
    public static function success($message, $redirect_url = null, $code = 0)
    {
        if (request::is_ajax()) {
            self::set('error', $code);
            self::set('message', $message);
            if ($redirect_url !== null) self::set('redirect_url', $redirect_url);
            self::ajax();
        } else {
            self::set_message($message, 'success');

            if ($redirect_url === null) $redirect_url = $_SERVER['HTTP_REFERER'];
            header('location:' . $redirect_url);
            exit();
        }
    }

    /**
     * 失败
     *
     * @param string $message 消息
     * @param string $redirect_url 跳转网址
     * @param int $code 错误码
     */
    public static function error($message, $redirect_url = null, $code = 1)
    {
        if (request::is_ajax()) {
            self::set('error', $code);
            self::set('message', $message);
            if ($redirect_url !== null) self::set('redirect_url', $redirect_url);
            self::ajax();
        } else {
            self::set_message($message, 'error');

            if ($redirect_url === null) $redirect_url = $_SERVER['HTTP_REFERER'];
            header('location:' . $redirect_url);
            exit();
        }
    }

    /**
     * 显示模板
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     */
    public static function display($template = null, $theme = null)
    {
        $template_instance = null;
        if ($template === null) {
            $controller = request::request('controller');
            $task = request::request('task', 'index');
            $template = $controller . '.' . $task;

            if (defined('IS_BACKEND') && IS_BACKEND) {
                $template_instance = be::get_admin_template($template, $theme);
            } else {
                $template_instance = be::get_template($template, $theme);
            }

        } else {
            if (defined('IS_BACKEND') && IS_BACKEND) {
                $template_instance = be::get_admin_template($template, $theme);
            } else {
                $template_instance = be::get_template($template, $theme);
            }
        }

        foreach (self::$data as $key => $val) {
            $template_instance->$key = $val;
        }

        if (session::has('_message')) {
            $template_instance->_message = session::delete('_message');
        }

        $template_instance->display();
    }

    /**
     * 获取模板内容
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return  string
     */
    public static function fetch($template, $theme = null)
    {
        ob_start();
        ob_clean();
        self::display($template, $theme);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * 结束输出
     *
     * @param string $string 输出内空
     * @return  string
     */
    public static function end($string = null)
    {
        if (session::has('_message')) {
            session::delete('_message');
        }

        if ($string === null) {
            exit;
        } else {
            exit('<!DOCTYPE html><html><head><meta charset="utf-8" /></head><body><div style="padding:10px;text-align:center;">' . $string . '</div></body></html>');
        }
    }

    /*
     * 封装 set_xxx 方法
     */
    public static function __callStatic($fn, $args)
    {
        if (substr($fn, 0, 4) == 'set_' && count($args) == 1) {
            self::$data[substr($fn, 4)] = $args[0];
        }
    }

}

