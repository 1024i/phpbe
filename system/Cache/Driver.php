<?php
namespace System\Cache;

/**
 * 缓存驱动
 */
interface Driver
{

    /**
     * 构造函数
     *
     * @param string $options 初始化参数
     */
    public function __construct($options = array());

    /**
     * 获取 指定的缓存 值
     *
     * @param string $key     键名
     * @return mixed
     */
    public function get($key);

    /**
     * 获取 多个指定的缓存 值
     *
     * @param array $keys    键名 数组
     * @return mixed
     */
    public function getMulti($keys);

    /**
     * 设置缓存
     *
     * @param string $key    键名
     * @param mixed  $value  值
     * @param int    $expire 有效时间（秒）
     * @return bool
     */
    public function set($key, $value, $expire = 0);

    /**
     * 设置缓存
     *
     * @param array $values 键值对
     * @param int   $expire 有效时间（秒）
     * @return bool
     */
    public function setMulti($values, $expire = 0);

    /**
     * 指定键名的缓存是否存在
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function has($key);

    /**
     * 删除指定键名的缓存
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function delete($key);

    /**
     * 自增缓存（针对数值缓存）
     *
     * @param string $key  缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function increment($key, $step = 1);

    /**
     * 自减缓存（针对数值缓存）
     *
     * @param string $key  缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function decrement($key, $step = 1);

    /**
     * 清除缓存
     *
     * @return bool
     */
    public function flush();

}
