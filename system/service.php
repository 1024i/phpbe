<?php
namespace system;

/**
 * 服务基类
 * 服务用于实于业务逻辑，提供服务，供控制器(controller)或其它服务(service)调用
 */
abstract class service extends obj
{

    protected $db = 'master';

    /**
     * 获取数据库连接
     */
    protected function db($db = null) {
        if ($db === null ) $db = $this->db;
        return db\factory::get_instance($db);
    }

}