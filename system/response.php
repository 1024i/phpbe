<?php
namespace system;

/**
 * response
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
     * @param string $value 值 (可以是数组或对象)
     */
    public static function set($name, $value)
    {
        self::$data[$name] = $value;
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
     */
    public static function success($message, $redirect_url = null)
    {
        if (request::is_ajax()) {
            self::set('error', 0);
            self::set('message', $message);
            self::set('redirect_url', $redirect_url);
            self::ajax();
        } else {
            $data = new \stdClass();
            $data->type = 'success';
            $data->body = $message;
            session::set('_message', $data);
            header('location:' . $redirect_url);
            exit();
        }
    }

    /**
     * 失败
     *
     * @param string $message 消息
     * @param string $redirect_url 跳转网址
     */
    public static function error($message, $redirect_url = null)
    {
        if (request::is_ajax()) {
            self::set('error', 1);
            self::set('message', $message);
            self::set('redirect_url', $redirect_url);
            self::ajax();
        } else {
            $data = new \stdClass();
            $data->type = 'error';
            $data->body = $message;
            session::set('_message', $data);
            header('location:' . $redirect_url);
            exit();
        }
    }

    /**
     * 显示模板
     *
     * @param $template
     */
    public static function display($template, $theme = null)
    {
        $template_instance = null;
        if (substr($template, 0, 6) === 'admin.') {
            $theme_class_name = '\\admin\\theme\\theme';
            $theme_instance = new $theme_class_name();

            $template_class_name = '\\admin\\template\\' . str_replace('.', '\\', $template);
            $template_instance = new $template_class_name();
        } else {
            if ($theme === null) {
                $config = be::get_config('system');
                $theme = $config->theme;
            }

            $theme_class_name = '\\theme\\' . $theme . '\\' . $theme;
            $theme_instance = new $theme_class_name();

            $template_class_name = '\\template\\' . str_replace('.', '\\', $template);
            $template_instance = new $template_class_name();
        }

        $template_instance->set_theme($theme_instance);
        $template_instance->set(self::$data);

        $message = session::get('_message');
        if ($message) {
            $template_instance->set('_message', $message);
        }

        $template_instance->display();
    }



    public static function end($string)
    {
        exit('<!DOCTYPE html><html><head><meta charset="utf-8" /></head><body><div style="padding:10px;text-align:center;">' . $string . '</div></body></html>');
    }

}

