<?php
namespace System\cache\dDriver;

use System\Cache\Exception;
use System\Cache\DriverInterface;

/**
 * Memcache 缓存类
 */
class Memcached implements DriverInterface
{

    /**
     * @var object
     */
    protected $handler = null;

    /**
     * 构造函数
     *
     * @param array $options 初始化参数
     * @throws Exception
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('memcached')) throw new Exception('服务器未安装 memcached 扩展！');

        if (empty($options)) throw new Exception('memcached 配置错误！');

        $this->handler = new \Memcached;
        $this->handler->addServers($options);
    }

    /**
     * 获取 指定的缓存 值
     *
     * @param string $key 键名
     * @param mixed $default 默认值
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
    public function getMulti($keys)
    {
        $prefixedKeys = array();
        foreach ($keys as $key) {
            $prefixedKeys[] = 'cache:'.$key;
        }

        $casTokens = null;
        $values = $this->handler->getMulti($prefixedKeys, $casTokens, \Memcached::GET_PRESERVE_ORDER);

        if ($this->handler->getResultCode() != 0) {
            return array_fill_keys($keys, false);
        }

        return array_combine($keys, $values);
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
            return $this->handler->set('cache:'.$key, $value, $expire);
        } else {
            return $this->handler->set('cache:'.$key, $value);
        }
    }

    /**
     * 设置缓存
     *
     * @param array $values 键值对
     * @param int $expire  有效时间（秒）
     * @return bool
     */
    public function setMulti($values, $expire = 0)
    {
        $formattedValues = array();
        foreach ($values as $key=>$value) {
            $formattedValues['cache:'.$key] = $value;
        }

        return $this->handler->setMulti($formattedValues, $expire);
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
