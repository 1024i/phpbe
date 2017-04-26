<?php
namespace system\cache\driver;

/**
 * memcache 缓存类
 */
class memcache extends \system\cache\driver
{

    /**
     * @var object
     */
    protected $handler = null;

    /**
     * 构造函数
     *
     * @param array $options 初始化参数
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('memcache')) be_exit('服务器未安装 memcache 扩展！');

        if (empty($options)) be_exit('memcache 配置错误！');

        $this->handler = new \Memcache;
        foreach ($options as $option) {
            if ($option['timeout'] > 0) {
                $this->handler->addServer($option['host'], $option['port'], $option['persistent'], $option['weight'], $option['timeout']);
            } else {
                $this->handler->addServer($option['host'], $option['port'], $option['persistent'], $option['weight']);
            }
        }
    }

    /**
     * 获取 指定的缓存 值
     *
     * @param string $key 键名
     * @return mixed|false
     */
    public function get($key)
    {
        return $this->handler->get('cache:'.$key);
    }

    /**
     * 获取 多个指定的缓存 值
     *
     * @param array $keys 键名 数组
     * @return array()
     */
    public function get_multi($keys)
    {
        $prefixed_keys = array();
        foreach ($keys as $key) {
            $prefixed_keys[] = 'cache:'.$key;
        }

        $return = array();
        foreach ($prefixed_keys as $index=>$prefixed_key) {
            $return[$keys[$index]] = $this->handler->get($prefixed_key);
        }
        return $return;
    }

    /**
     * 设置缓存
     *
     * @param string $key 键名
     * @param string $value 值
     * @param int $expire  有效时间（秒）
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        if ($expire>0) {
            return $this->handler->set('cache:'.$key, $value, 0 , $expire);
        } else {
            return $this->handler->set('cache:'.$key, $value, 0);
        }
    }

    /**
     * 设置缓存
     *
     * @param array $values 键值对
     * @param int $expire  有效时间（秒）
     * @return bool
     */
    public function set_multi($values, $expire = 0)
    {
        if ($expire>0) {
            foreach ($values as $key=>$val) {
                $this->handler->set('cache:'.$key, $val, 0, $expire);
            }
        } else {
            foreach ($values as $key=>$val) {
                $this->handler->set('cache:'.$key, $val);
            }
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
        return $this->handler->get('cache:'.$key) ? true : false;
    }

    /**
     * 删除指定键名的缓存
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function delete($key)
    {
        return $this->handler->delete('cache:'.$key);
    }

    /**
     * 自增缓存（针对数值缓存）
     *
     * @param string    $key 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function increment($key, $step = 1)
    {
        return $this->handler->increment('cache:'.$key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * 因为 memcache decrement 不支持负数，因此没有使用原生的 decrement
     *
     * @param string    $key 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function decrement($key, $step = 1)
    {
        $value = $this->handler->get('cache:'.$key);
        if ($value ===false) $value = 0;
        $value -= $step;
        $this->handler->set('cache:'.$key, $value);
        return $value;
    }

    /**
     * 清除缓存
     *
     * @return bool
     */
    public function flush()
    {
        return $this->handler->flush();
    }

}
