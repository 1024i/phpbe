<?php
namespace system;


class loader
{

    /**
     * 自动加载
     *
     * @param string $class 类名
     * @return void
     */
    public static function autoload($class)
    {
        $class = trim($class);
        if (substr($class, 0, 1) != '\\') $class = '\\' . $class;

        //echo $class . '<br>';

        $paths = explode('\\', $class);
        if ($paths[1] == 'admin') {
            $paths[1] = ADMIN;
        } elseif ($paths[1] == 'data') {
            $paths[1] = DATA;
        }

        $path = PATH_ROOT . implode(DS,  $paths) . '.php';
        if (is_file($path)) include $path;
    }

}

