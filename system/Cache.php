<?php
namespace System;

/**
 * 缓存类
 *
 * @method mixed get(string $key, mixed $default = null) static 获取 指定的缓存 值
 * @method array multiGet(array $keys, mixed $default = null) static 获取 多个指定的缓存 值
 * @method bool set(string $key, mixed $value, int $expire = 0) static 设置缓存
 * @method bool multiSet(array $values, int $expire = 0) static 设置缓存
 * @method bool has(string $key) static 指定键名的缓存是否存在
 * @method bool delete(string $key) static 删除指定键名的缓存
 * @method int increment(string $key, int $step = 1) static 自增缓存（针对数值缓存）
 * @method int decrement(string $key, int $step = 1) static 自减缓存（针对数值缓存）
 * @method bool flush() static 清除缓存
 *
 */
class Cache
{

	/**
	 * 缓存实例
	 */
	private static $handler = null;


	/**
	 * 初始化
	 */
	private static function init()
	{
		if (self::$handler === null) {
			$configCache = Be::getConfig('cache');
			$driver = $configCache->driver;

			$className = '\\System\\Cache\\Driver\\' . $driver . 'Impl';
			$options = isset($configCache->$driver) ? $configCache->$driver : array();

			self::$handler = new $className($options);
		}
	}


	/**
     * 封装 获取资源方法
	 *
	 * @param string $fn 方法名
	 * @param array() $args 传入的参数
	 * @return mixed
     */
	public static function __callStatic($fn, $args)
	{
		self::init();
		return call_user_func_array(array(self::$handler, $fn), $args);
	}

}