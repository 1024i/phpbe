<?php
namespace app\system\tool;

class system
{
    /**
     * 处理网址
     *
     * @param string $url 要处理的网址，启用 SEF 时生成伪静态页， 为空时返回网站网址
     * @return string
     */
    public static function url($url)
    {
        $config_system = \system\be::get_config('system.system');
        if ($config_system->sef) {
            $urls = explode('&', $url);

            if (count($urls) == 0) return URL_ROOT;

            $app = null;
            $controller = null;
            $task = null;
            $params = array();

            foreach ($urls as $x) {
                $pos = strpos($x, '=');

                if ($pos !== false) {
                    $key = substr($x, 0, $pos);
                    $val = substr($x, $pos+1);

                    if ($key == 'app') {
                        $app = $val;
                    } elseif ($key == 'controller') {
                        $controller = $val;
                    } elseif ($key == 'task') {
                        $task = $val;
                    } else {
                        $params[$key] = $val;
                    }
                }
            }

            if ($app === null) return URL_ROOT;
            if ($controller === null) return URL_ROOT . '/' . $app . $config_system->sef_suffix;
            if ($task == null) return URL_ROOT . '/' . $controller . $config_system->sef_suffix;

            $router = \system\be::get_router($app, $controller);
            return $router->encode_url($app, $controller, $task, $params);
        }

        return URL_ROOT . '/?' . $url;
    }

    /**
     * 处理后台网址
     *
     * @param string $url 要处理的网址，启用 SEF 时生成伪静态页， 为空时返回网站网址
     * @return string
     */
    public static function admin_url($url)
    {
        $config_system = \system\be::get_config('system.system');
        if ($config_system->sef) {
            $urls = explode('&', $url);

            if (count($urls) == 0) return URL_ADMIN;

            $app = null;
            $admin_controller = null;
            $task = null;
            $params = array();

            foreach ($urls as $x) {
                $pos = strpos($x, '=');

                if ($pos !== false) {
                    $key = substr($x, 0, $pos);
                    $val = substr($x, $pos+1);

                    if ($key == 'app') {
                        $app = $val;
                     } elseif ($key == 'admin_controller') {
                        $admin_controller = $val;
                    } elseif ($key == 'task') {
                        $task = $val;
                    } else {
                        $params[$key] = $val;
                    }
                }
            }

            if ($app === null) return URL_ADMIN;
            if ($admin_controller === null) return URL_ADMIN . '/' . $app . $config_system->sef_suffix;
            if ($task == null) return URL_ADMIN . '/' . $admin_controller . $config_system->sef_suffix;

            $router = \system\be::get_admin_router($app, $admin_controller);
            return $router->encode_url($app, $admin_controller, $task, $params);
        }

        return URL_ADMIN . '/?' . $url;
    }

}
