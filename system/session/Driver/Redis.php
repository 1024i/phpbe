<?php
namespace System\Session\Driver;

use System\Response;

/**
 * Redis session
 */
class Redis extends \SessionHandler
{

	private $expire = 1440; // session 超时时间

	/**
	 * @var \redis
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
		if (!extension_loaded('Redis')) Response::end('SESSION 初始化失败：服务器未安装 Redis 扩展！');

		if (isset($configSession->redis)) {
			$this->options = $configSession->redis;
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
		if ($options !== null) {
			$this->handler = new \Redis;
			$fn = $options['persistent'] ? 'pconnect' : 'connect';
			if ($options['timeout']>0)
				$this->handler->$fn($options['host'],$options['port'], $options['timeout']);
			else
				$this->handler->$fn($options['host'],$options['port']);
			if ('' != $options['password']) $this->handler->auth($options['password']);
			if (0 != $options['db']) $this->handler->select($options['db']);
		} else {
			$this->handler = \System\Redis::getInstance();
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
		return $this->handler->setex('session:'.$sessionId, $this->expire, $sessionData);
	}
	/**
	 * 销毁 session
	 *
	 * @param int $sessionId 要销毁的 session 的 session id
	 * @return bool
	 */
	public function destroy($sessionId) {
		return $this->handler->del('session:'.$sessionId);
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
