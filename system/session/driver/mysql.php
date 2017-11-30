<?php
namespace system\session\driver;

use system\response;
use system\be;

/**
 * mysql session
 *
 * SESSION 表结构：
 *
    CREATE TABLE IF NOT EXISTS `session` (
    `session_id` varchar(60) NOT NULL COMMENT 'session id',
    `session_data` varchar(21782) NOT NULL COMMENT 'session 数据',
    `expire` int(10) unsigned NOT NULL COMMENT '超时时间',
    PRIMARY KEY (`session_id`),
    KEY `expire` (`expire`)
   ) ENGINE=MEMORY DEFAULT CHARSET=utf8;
 *
 * 注意，因为mysql内存表的限制(不能使用text)，varchar最大存储长度有限制
 *
 */
class mysql extends \SessionHandler
{

    private $expire = 1440; // session 超时时间

    /**
     * @var \PDO
     */
    private $handler = null;
    private $options = null;
    private $table = 'session'; // 存放 session 的表名

    /**
     * 构造函数
     *
     * @param object $config_session session 配直参数
     */
    public function __construct($config_session)
    {
        if (isset($config_session->mysql)) {
            $this->options = $config_session->mysql;
        } else {
            response::end('SESSION 配置 mysql 参数错误！');
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

        if (isset($options['host'])) {
            $this->handler = new \PDO('mysql:dbname='.$options['name'].';host='.$options['host'].';port='.$options['port'].';charset=utf8', $options['user'], $options['pass']);
            if (!$this->handler) response::end('连接 数据库'.$options['name'].'（'.$options['host'].'） 失败！');

            // 设置默认编码为 UTF-8 ，UTF-8 为 PHPBE 默认标准字符集编码
            $this->handler->query('SET NAMES utf8');
        } else {
            $db = be::get_db();
            $this->handler = $db->get_connection();
        }

        $this->table = $options['table'];

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
        $sql = 'SELECT session_data FROM '.$this->table.' WHERE session_id=? AND expire>?';
        $statement = $this->handler->prepare($sql);
        if (!$statement->execute(array($session_id, time()))) return '';
        $data = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($data && isset($data['session_data'])) return $data['session_data'];
        return '';
    }

    /**
     * 写入 session 数据
     *
     * @param string $session_id session id
     * @param string $session_data 写入 session 的数据
     * @return bool
     */
    public function write($session_id, $session_data) {
        $expire = time() + $this->expire;

        $sql = 'INSERT INTO '.$this->table.'(session_id, session_data, expire)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE session_data=?, expire=?';
        $statement = $this->handler->prepare($sql);
        return $statement->execute(array($session_id, $session_data, $expire, $session_data, $expire));
    }

    /**
     * 销毁 session
     *
     * @param int $session_id 要销毁的 session 的 session id
     * @return bool
     */
    public function destroy($session_id) {
        $sql = 'DELETE FROM '.$this->table.' WHERE session_id = ?';
        $statement = $this->handler->prepare($sql);
        return $statement->execute(array($session_id));
    }

    /**
     * 垃圾回收
     *
     * @param int $max_lifetime 最大生存时间
     * @return bool
     */
    public function gc($max_lifetime) {
        $sql = 'DELETE FROM '.$this->table.' WHERE expire<?';
        $statement = $this->handler->prepare($sql);
        return $statement->execute(array(time()));
    }

}
