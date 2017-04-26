<?php
namespace system\session\driver;

/**
 * redis session
 */
class redis extends \SessionHandler
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
	 * @param object $config_session session 配直参数
	 */
	public function __construct($config_session)
	{
		if (!extension_loaded('redis')) be_exit('SESSION 初始化失败：服务器未安装 redis 扩展！');

		if (isset($config_session->redis)) {
			$this->options = $config_session->redis;
		}
		$this->expire = $config_session->expire;
	}

	/**
	 * 初始化 session
	 *
	 * @param string $save_path 保存路径
	 * @param string $session_id session id
	 * @return bool
	 */
	public function open($save_path, $session_id) {
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
			$this->handler = \system\redis::get_instance();
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
	 * @param string $session_id session id
	 * @return string
	 */
	public function read($session_id) {
		return $this->handler->get('session:'.$session_id);
	}

	/**
	 * 写入 session 数据
	 *
	 * @param string $session_id session id
	 * @param string $session_data 写入 session 的数据
	 * @return bool
	 */
	public function write($session_id, $session_data) {
		return $this->handler->setex('session:'.$session_id, $this->expire, $session_data);
	}
	/**
	 * 销毁 session
	 *
	 * @param int $session_id 要销毁的 session 的 session id
	 * @return bool
	 */
	public function destroy($session_id) {
		return $this->handler->del('session:'.$session_id);
	}

	/**
	 * 垃圾回收
	 *
	 * @param int $max_lifetime 最大生存时间
	 * @return bool
	 */
	public function gc($max_lifetime) {
		return true;
	}

}
