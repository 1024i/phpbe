<?php

namespace system;

/**
 * error_log
 */
class error_log
{
    /*
     * 错误或异常信息写入日志文件
     *
     * @param Throwable $e 错误或异常对象
     * @author Lou Barnes<i@liu12.com>
     */
    public static function log(\Throwable $e)
    {
        $config_system = be::get_config('system.system');
        if ($config_system->error_log & $e->getCode()) return;

        $type = null;
        if ($e instanceof \Exception) {
            $type = 'exception';
        } elseif ($e instanceof \Error) {
            $type = 'error';
        }

        $t = time();

        $error = [
            'type' => $type,
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
            'POST' => &$_POST,
            'GET' => &$_GET,
            'COOKIE' => &$_COOKIE,
            'SESSION' => &$_SESSION,
            'SERVER' => &$_SERVER,
            'REQUEST' => &$_REQUEST,
            'time' => $t,
        ];

        $year = date('Y', $t);
        $month = date('m', $t);
        $day = date('d', $t);

        $path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $year . DS . $month . DS . $day . '.data';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $offset = 0;
        if (is_file($path)) $offset = filesize($path);

        // 单个日志文件大于 256 M 时，创建新的日志文件
        $i = 1;
        while ($offset > 256 * 1024 * 1024) {
            $day = date('d', $t) . '-' . $i;
            $path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $year . DS . $month . DS . $day . '.data';
            $offset = 0;
            if (file_exists($path)) $offset = filesize($path);
            $i++;
        }

        $error_data = serialize($error);
        $f = fopen($path, 'ab+');
        if ($f) {
            fwrite($f, $error_data);
            fclose($f);
        }

        $path = PATH_DATA . DS . 'system' . DS . 'error_log' . DS . $year . DS . $month . DS . $day . '.index';
        $f = fopen($path, 'ab+');
        if ($f) {
            fwrite($f, pack('L', $offset));
            fclose($f);
        }
    }
}
