<?php
namespace System\Redis;

/**
 * Redis 类
 */
class Driver
{

    private $instance = null; // 数据库连接

    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 连接数据库
     *
     * @throws RedisException
     */
    public function connect()
    {
        if ($this->instance === null) {

            if (!extension_loaded('Redis')) throw new RedisException('服务器未安装 Redis 扩展！');

            $config = $this->config;

            $instance = new \Redis();
            $fn = $config['persistent'] ? 'pconnect' : 'connect';
            if ($config['timeout'] > 0)
                $instance->$fn($config['host'], $config['port'], $config['timeout']);
            else
                $instance->$fn($config['host'], $config['port']);
            if ('' != $config['password']) $instance->auth($config['password']);
            if (0 != $config['db']) $instance->select($config['db']);

            $this->instance = $instance;
        }
    }

    /**
     * 获取 redis 实例
     *
     * @return \redis
     */
    public function getInstance()
    {
        $this->connect();
        return $this->instance;
    }

    /**
     * 封装 redis 方法
     *
     * @param string $fn redis 扩展方法名
     * @param array() $args 传入的参数
     * @return mixed
     */
    public function __call($fn, $args)
    {
        $this->connect();
        return call_user_func_array(array($this->instance, $fn), $args);
    }

}
