<?php
namespace system\session\driver;

use system\response;

/**
 * memcache session
 */
class memcache extends \SessionHandler
{

	private $expire = 1440; // session 超时时间

	/**
	 * @var \memcache
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
		if (!extension_loaded('memcache')) response::end('SESSION 初始化失败：服务器未安装 memcache 扩展！');

		if (isset($config_session->memcache)) {
			$this->options = $config_session->memcache;
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
		if ($options === null) {
			response::end('SESSION 初始化失败：memcache 配置参数错误！');
		} else {
			$this->handler = new \Memcache;
			foreach ($options as $option) {
				if ($option['timeout'] > 0) {
					$this->handler->addServer($option['host'], $option['port'], $option['persistent'], $option['weight'], $option['timeout']);
				} else {
					$this->handler->addServer($option['host'], $option['port'], $option['persistent'], $option['weight']);
				}
			}
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
		return $this->handler->set('session:'.$session_id, $session_data, 0 , $this->expire);
	}
	/**
	 * 销毁 session
	 *
	 * @param int $session_id 要销毁的 session 的 session id
	 * @return bool
	 */
	public function destroy($session_id) {
		return $this->handler->delete('session:'.$session_id);
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
