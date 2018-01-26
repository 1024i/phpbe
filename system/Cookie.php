<?php
namespace System;

/**
 * cookie
 */
class Cookie
{

	private static $expire = 0;
    private static $path = '/';
	private static $domain = '';
	private static $secure = false;


	// 设置默认超时时间
	public static function setExpire($expire)
	{
		if (!is_numeric($expire)) $expire = 0;
		self::$expire = $expire;
	}

	// 设置默认路径
	public static function setPath($path)
	{
		self::$path = $path;
	}

	// 设置默认域名
	public static function setDomain($domain)
	{
		self::$domain = $domain;
	}

	public static function setSecure($secure)
	{
		self::$secure = $secure;
	}

	/**
	 * 获取 cookie 值
	 *
	 * @param string $name 名称
	 * @param string $default 默认值
	 * @return string
	 */
	public static function get($name, $default = null)
	{
		if (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return $default;
	}


    /**
     * 向 cookie 中赋值
	 *
     * @param string $name 名称
     * @param string $value 值
	 * @param int $expire 超时时间
	 * @param string $path 路径
	 * @param string $domain 域名
	 * @param bool $secure 是否加密
	 * @return bool
     */
	public static function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null)
	{
		if ($expire === null) $expire = self::$expire;
		if ($path === null) $path = self::$path;
		if ($domain === null) $domain = self::$domain;
		if ($secure === null) $secure = self::$secure;

		return setcookie($name, $value, $expire, $path, $domain, $secure);
	}


    /**
     * 是否已设置指定名称的 cookie
	 *
     * @param string $name 名称
	 * @return bool
     */
	public static function has($name)
	{
		return isset($_COOKIE[$name]);
	}

    /**
     * 删除指定名称的 cookie
	 *
     * @param string $name 名称
	 * @param string $path 路径
	 * @param string $domain 域名
	 * @param bool $secure 是否加密
	 * @return string|null
     */
	public static function delete($name, $path = null, $domain = null, $secure = null)
	{
		$value = null;

		if (cookie::has($name)) {
			$value = cookie::get($name);

			if ($path === null) $path = self::$path;
			if ($domain === null) $domain = self::$domain;
			if ($secure === null) $secure = self::$secure;

			setcookie($name, '', time()-1, $path, $domain, $secure);
		}

		return $value;
	}

}
