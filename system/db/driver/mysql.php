<?php

namespace system\Db\Driver;

use System\Be;
use System\Db\Driver;
use System\Db\Exception;

/**
 * 数据库类
 */
class Mysql extends Driver
{

    /**
     * 连接数据库
     *
     * @return bool 是否连接成功
     * @throws
     */
    public function connect()
    {
        $config = $this->config;
        $connection = new \PDO('mysql:dbname=' . $config['name'] . ';host=' . $config['host'] . ';port=' . $config['port'] . ';charset=utf8', $config['user'], $config['pass']);
        if (!$connection) throw new Exception('连接 数据库' . $config['name'] . '（' . $config['host'] . '） 失败！');

        // 设置默认编码为 UTF-8 ，UTF-8 为 PHPBE 默认标准字符集编码
        $connection->query('SET NAMES utf8');

        $this->connection = $connection;
        return true;
    }

    /**
     * 插入一个对象到数据库
     *
     * @param string $table 表名
     * @param object /array(object) $obj 要插入数据库的对象或对象数组，对象属性需要和该表字段一致
     * @return bool
     */
    public function insert($table, $obj)
    {
        // 批量插入
        if (is_array($obj)) {
            $vars = get_object_vars($obj[0]);
            $sql = 'INSERT INTO `' . $table . '`(`' . implode('`,`', array_keys($vars)) . '`) VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
            $this->prepare($sql);
            foreach ($obj as $o) {
                $vars = get_object_vars($o);
                $this->execute(null, array_values($vars));
            }
        } else {
            $vars = get_object_vars($obj);
            $sql = 'INSERT INTO `' . $table . '`(`' . implode('`,`', array_keys($vars)) . '`) VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
            $this->execute($sql, array_values($vars));
        }
        return true;
    }

    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param object $obj 要插入数据库的对象，对象属性需要和该表字段一致
     * @param string $primaryKey 主键
     * @return bool
     * @throws Exception
     */
    public function update($table, $obj, $primaryKey)
    {
        $fields = array();
        $fieldValues = array();

        $where = null;
        $whereValue = null;

        foreach (get_object_vars($obj) as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            // 主键不更新
            if ($key == $primaryKey) {
                $where = '`' . $key . '`=?';
                $whereValue = $value;
                continue;
            }
            if ($value === null) {
                continue;
            } else {
                $fields[] = '`' . $key . '`=?';
                $fieldValues[] = $value;
            }
        }

        if ($where == null) {
            throw new Exception('更新数据时未指定条件！');
        }

        $sql = 'UPDATE `' . $table . '` SET ' . implode(',', $fields) . ' WHERE ' . $where;
        $fieldValues[] = $whereValue;

        return $this->execute($sql, $fieldValues);
    }

    /**
     * 获取一个表的字段列表
     *
     * @param string $table 表名
     * @return array
     */
    public function getTableFields($table)
    {
        $fields = $this->getObjects('SHOW FIELDS FROM `' . $table . '`');

        $data = array();
        foreach ($fields as $field) {
            $data[$field->Field] = $field;
        }
        return $data;
    }

    /**
     * 删除表
     *
     * @param string $table 表名
     * @return bool
     */
    public function dropTable($table)
    {
        return $this->execute('DROP TABLE IF EXISTS `' . $table . '`');
    }

}