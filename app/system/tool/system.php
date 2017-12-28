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
        $configSystem = \system\Be::getConfig('System.System');
        if ($configSystem->sef) {
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
            if ($controller === null) return URL_ROOT . '/' . $app . $configSystem->sefSuffix;
            if ($task == null) return URL_ROOT . '/' . $controller . $configSystem->sefSuffix;

            $router = \system\Be::getRouter($app, $controller);
            return $router->encodeUrl($app, $controller, $task, $params);
        }

        return URL_ROOT . '/?' . $url;
    }

    /**
     * 处理后台网址
     *
     * @param string $url 要处理的网址，启用 SEF 时生成伪静态页， 为空时返回网站网址
     * @return string
     */
    public static function adminUrl($url)
    {
        $configSystem = \system\Be::getConfig('System.System');
        if ($configSystem->sef) {
            $urls = explode('&', $url);

            if (count($urls) == 0) return URL_ADMIN;

            $app = null;
            $adminController = null;
            $task = null;
            $params = array();

            foreach ($urls as $x) {
                $pos = strpos($x, '=');

                if ($pos !== false) {
                    $key = substr($x, 0, $pos);
                    $val = substr($x, $pos+1);

                    if ($key == 'app') {
                        $app = $val;
                     } elseif ($key == 'adminController') {
                        $adminController = $val;
                    } elseif ($key == 'task') {
                        $task = $val;
                    } else {
                        $params[$key] = $val;
                    }
                }
            }

            if ($app === null) return URL_ADMIN;
            if ($adminController === null) return URL_ADMIN . '/' . $app . $configSystem->sefSuffix;
            if ($task == null) return URL_ADMIN . '/' . $adminController . $configSystem->sefSuffix;

            $router = \system\Be::getAdminRouter($app, $adminController);
            return $router->encodeUrl($app, $adminController, $task, $params);
        }

        return URL_ADMIN . '/?' . $url;
    }

}
