<?php
namespace System\Session\Driver;

use System\Response;

/**
 * Memcached session
 */
class MemcachedImpl extends \SessionHandler
{

	private $expire = 1440; // session 超时时间

	/**
	 * @var \memcached
	 */
	private $handler = null;
	private $options = null;

	/**
	 * 构造函数
	 *
	 * @param object $configSession session 配直参数
	 */
	public function __construct($configSession)
	{
		if (!extension_loaded('Memcached')) Response::end('SESSION 初始化失败：服务器未安装 memcached 扩展！');

		if (isset($configSession->memcached)) {
			$this->options = $configSession->memcached;
		}
		$this->expire = $configSession->expire;
	}

	/**
	 * 初始化 session
	 *
	 * @param string $savePath 保存路径
	 * @param string $sessionId session id
	 * @return bool
	 */
	public function open($savePath, $sessionId) {
		$options = $this->options;
		if ($options === null) {
			Response::end('SESSION 初始化失败：memcached 配置参数错误！');
		} else {
			$this->handler = new \Memcached;
			$this->handler->addServers($options);
		}
		return true;
	}

	/**
	 * 关闭 session
	 *
	 * @return bool
	 */
	public function close() {
		return true;
	}

	/**
	 * 讯取 session 数据
	 *
	 * @param string $sessionId session id
	 * @return string
	 */
	public function read($sessionId) {
		return $this->handler->get('session:'.$sessionId);
	}

	/**
	 * 写入 session 数据
	 *
	 * @param string $sessionId session id
	 * @param string $sessionData 写入 session 的数据
	 * @return bool
	 */
	public function write($sessionId, $sessionData) {
		return $this->handler->set('session:'.$sessionId, $sessionData, $this->expire);
	}

	/**
	 * 销毁 session
	 *
	 * @param int $sessionId 要销毁的 session 的 session id
	 * @return bool
	 */
	public function destroy($sessionId) {
		return $this->handler->delete('session:'.$sessionId);
	}

	/**
	 * 垃圾回收
	 *
	 * @param int $maxLifetime 最大生存时间
	 * @return bool
	 */
	public function gc($maxLifetime) {
		return true;
	}

}
