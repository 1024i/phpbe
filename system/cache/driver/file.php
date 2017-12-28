<?php
namespace system\cache\driver;

use \system\cache\Driver;

/**
 * 文件 缓存类
 */
class File extends Driver
{

    /**
     * 构造函数
     *
     * @param array $options 初始化参数
     */
    public function __construct($options = array())
    {
    }

    /**
     * 获取 指定的缓存 值
     *
     * @param string $key 键名
     * @return mixed|false
     */
    public function get($key)
    {
        $hash = sha1($key);
        $path = PATH_DATA . DS . 'System' . DS . 'Cache' . DS . 'File' . DS . substr($hash, 0, 2) . DS . substr($hash, 2, 2) . DS . $hash . '.php';

        if (!is_file($path)) return false;

        $content = file_get_contents($path);

        if (false !== $content) {
            $expire = substr($content, 8, 10);
            if (time() > intval($expire)) {
                unlink($path);
                return false;
            }

            $value = substr($content, 18);
            if (!is_numeric($value)) $value = unserialize($value);
            return $value;
        } else {
            return false;
        }
    }

    /**
     * 获取 多个指定的缓存 值
     *
     * @param array $keys 键名 数组
     * @return array()
     */
    public function getMulti($keys)
    {
        $values = array();
        foreach ($keys as $key) {
            $values[] = $this->get($key);
        }
        return $values;
    }

    /**
     * 设置缓存
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @param int $expire 有效时间（秒）
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        $hash = sha1($key);
        $dir = PATH_DATA . DS . 'System' . DS . 'Cache' . DS . 'File' . DS . substr($hash, 0, 2) . DS . substr($hash, 2, 2);
        if (!is_dir($dir)) mkdir($dir, 0777, 1);
        $path = $dir . DS . $hash . '.php';

        if (!is_numeric($value)) $value = serialize($value);

        if ($expire == 0) {
            $expire = 9999999999;
        } else {
            $expire = time() + $expire;
            if ($expire > 9999999999) $expire = 9999999999;
        }
        $data = "<?php\n//" . $expire . $value;
        return file_put_contents($path, $data);
    }

    /**
     * 设置缓存
     *
     * @param array $values 键值对
     * @param int $expire 有效时间（秒）
     * @return bool
     */
    public function setMulti($values, $expire = 0)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $expire);
        }
        return true;
    }

    /**
     * 指定键名的缓存是否存在
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function has($key)
    {
        $hash = sha1($key);
        $path = PATH_DATA . DS . 'System' . DS . 'Cache' . DS . 'File' . DS . substr($hash, 0, 2) . DS . substr($hash, 2, 2) . DS . $hash . '.php';

        return is_file($path) ? true : false;
    }

    /**
     * 删除指定键名的缓存
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function delete($key)
    {
        $hash = sha1($key);
        $path = PATH_DATA . DS . 'System' . DS . 'Cache' . DS . 'File' . DS . substr($hash, 0, 2) . DS . substr($hash, 2, 2) . DS . $hash . '.php';
        if (!is_file($path)) return true;
        return unlink($path);
    }

    /**
     * 自增缓存（针对数值缓存）
     *
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function increment($key, $step = 1)
    {
        $hash = sha1($key);
        $dir = PATH_DATA . DS . 'System' . DS . 'Cache' . DS . 'File' . DS . substr($hash, 0, 2) . DS . substr($hash, 2, 2);
        if (!is_dir($dir)) mkdir($dir, 0777, 1);
        $path = $dir . DS . $hash . '.php';

        if (!is_file($path)) {
            $value = $step;
            $data = "<?php\n//9999999999" . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        }

        $content = file_get_contents($path);

        if (false !== $content) {
            $expire = substr($content, 8, 10);
            if (time() > intval($expire)) return false;

            $content = substr($content, 18);
            $value = intval($content) + $step;
            $data = "<?php\n//" . $expire . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        } else {
            return false;
        }
    }

    /**
     * 自减缓存（针对数值缓存）
     *
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function decrement($key, $step = 1)
    {
        $hash = sha1($key);
        $dir = PATH_DATA . DS . 'System' . DS . 'Cache' . DS . 'File' . DS . substr($hash, 0, 2) . DS . substr($hash, 2, 2);
        if (!is_dir($dir)) mkdir($dir, 0777, 1);
        $path = $dir . DS . $hash . '.php';

        if (!is_file($path)) {
            $value = -$step;
            $data = "<?php\n//9999999999" . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        }

        $content = file_get_contents($path);

        if (false !== $content) {
            $expire = substr($content, 8, 10);
            if (time() > intval($expire)) return false;

            $content = substr($content, 18);
            $value = intval($content) - $step;
            $data = "<?php\n//" . $expire . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        } else {
            return false;
        }
    }

    /**
     * 清除缓存
     *
     * @return bool
     */
    public function flush()
    {
        $path = PATH_DATA . DS . 'System' . DS . 'Cache' . DS . 'File';

        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $this->rm($path . DS . $file);
            }
        }
        closedir($handle);

        return true;
    }

    /**
     * 递归删除文件及文件夹
     * @param string $path 文件路径
     */
    private function rm($path)
    {
        if (is_dir($path)) {
            $handle = opendir($path);
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $this->rm($path . DS . $file);
                }
            }
            closedir($handle);

            rmdir($path);
        } else {
            unlink($path);
        }
    }

}
