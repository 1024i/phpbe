<?php
namespace System\MongoDB;

/**
 * MongoDB
 */
class Driver
{

    private $connection = null; // mongodb 数据库连接
    private $db = null;
    private $collection = null;

    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 连接数据库
     *
     * @throws \Exception
     */
    public function connect()
    {
        if ($this->connection === null) {
            if (!extension_loaded('mongoDb')) throw new MongoDBException('服务器未安装mongoDb扩展！');

            $config = $this->config;

            $connection = new \MongoClient($config['host'] . ':' . $config['port']);
            $this->collection = $connection;

            $db = $config['db'];
            $this->db = $connection->$db; // 选择数据库
        }
    }

    /**
     * 获取 MongoDB 版本号
     *
     * @return string
     */
    public function getVersion()
    {
        return \MongoClient::VERSION;
    }

    /**
     * 获取 mongoDb 实例
     *
     * @return \mongoDb
     */
    public function getConnection()
    {
        $this->connect();
        return $this->connection;
    }

    /**
     * 切换数据库
     *
     * @param $db
     */
    public function setDb($db)
    {
        $this->connect();
        $connection = $this->connection;
        $this->db = $connection->$db; // 选择数据库
    }

    /**
     * 切换集合
     *
     * @param $collection
     * @throws \Exception
     */
    public function setCollection($collection)
    {
        $this->connect();

        if ($this->db === null) throw new MongoDBException('未选择数据库！');

        $db = $this->db;
        $this->collection = $db->$collection; // 选择数据库
    }

    /**
     * 封装 mongoDb 方法
     *
     * @param string $fn mongoDb 扩展方法名
     * @param array() $args 传入的参数
     * @return mixed
     * @throws \Exception
     */
    public function __call($fn, $args)
    {
        $this->connect();

        if ($this->db === null) throw new MongoDBException('未选择数据库！');
        if ($this->collection === null) throw new MongoDBException('未选择集合！');

        return call_user_func_array(array($this->collection, $fn), $args);
    }
}
