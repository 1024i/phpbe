<?php
namespace system\Session\Driver;

use System\Response;
use System\Be;

/**
 * mysql session
 *
 * SESSION 表结构：
 *
    CREATE TABLE IF NOT EXISTS `session` (
    `session_id` varchar(60) NOT NULL COMMENT 'session id',
    `session_data` varchar(21782) NOT NULL COMMENT 'session 数据',
    `expire` int(10) unsigned NOT NULL COMMENT '超时时间',
    PRIMARY KEY (`sessionId`),
    KEY `expire` (`expire`)
   ) ENGINE=MEMORY DEFAULT CHARSET=utf8;
 *
 * 注意，因为mysql内存表的限制(不能使用text)，varchar最大存储长度有限制
 *
 */
class Mysql extends \SessionHandler
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
     * @param object $configSession session 配直参数
     */
    public function __construct($configSession)
    {
        if (isset($configSession->mysql)) {
            $this->options = $configSession->mysql;
        } else {
            Response::end('SESSION 配置 mysql 参数错误！');
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

        if (isset($options['host'])) {
            $this->handler = new \PDO('mysql:dbname='.$options['name'].';host='.$options['host'].';port='.$options['port'].';charset=utf8', $options['user'], $options['pass']);
            if (!$this->handler) Response::end('连接 数据库'.$options['name'].'（'.$options['host'].'） 失败！');

            // 设置默认编码为 UTF-8 ，UTF-8 为 PHPBE 默认标准字符集编码
            $this->handler->query('SET NAMES utf8');
        } else {
            $db = Be::getDb();
            $this->handler = $db->getConnection();
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
     * @param string $sessionId session id
     * @return string
     */
    public function read($sessionId) {
        $sql = 'SELECT session_data FROM '.$this->table.' WHERE sessionId=? AND expire>?';
        $statement = $this->handler->prepare($sql);
        if (!$statement->execute(array($sessionId, time()))) return '';
        $data = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($data && isset($data['sessionData'])) return $data['sessionData'];
        return '';
    }

    /**
     * 写入 session 数据
     *
     * @param string $sessionId session id
     * @param string $sessionData 写入 session 的数据
     * @return bool
     */
    public function write($sessionId, $sessionData) {
        $expire = time() + $this->expire;

        $sql = 'INSERT INTO '.$this->table.'(session_id, session_data, expire)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE session_data=?, expire=?';
        $statement = $this->handler->prepare($sql);
        return $statement->execute(array($sessionId, $sessionData, $expire, $sessionData, $expire));
    }

    /**
     * 销毁 session
     *
     * @param int $sessionId 要销毁的 session 的 session id
     * @return bool
     */
    public function destroy($sessionId) {
        $sql = 'DELETE FROM '.$this->table.' WHERE session_id = ?';
        $statement = $this->handler->prepare($sql);
        return $statement->execute(array($sessionId));
    }

    /**
     * 垃圾回收
     *
     * @param int $maxLifetime 最大生存时间
     * @return bool
     */
    public function gc($maxLifetime) {
        $sql = 'DELETE FROM '.$this->table.' WHERE expire<?';
        $statement = $this->handler->prepare($sql);
        return $statement->execute(array(time()));
    }

}
