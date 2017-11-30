<?php

namespace system\db\driver;

use system\be;
use system\db\driver;
use system\db\exception;

/**
 * 数据库类
 */
class mysql extends driver
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
        if (!$connection) throw new exception('连接 数据库' . $config['name'] . '（' . $config['host'] . '） 失败！');

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
            return true;
        } else {
            $vars = get_object_vars($obj);
            $sql = 'INSERT INTO `' . $table . '`(`' . implode('`,`', array_keys($vars)) . '`) VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
            if (!$this->execute($sql, array_values($vars))) return false;
            return true;
        }
    }

    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param object $obj 要插入数据库的对象，对象属性需要和该表字段一致
     * @param string $primary_key 主键
     * @return bool
     */
    public function update($table, $obj, $primary_key)
    {
        $fields = array();
        $field_values = array();

        $where = null;
        $where_value = null;

        foreach (get_object_vars($obj) as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            // 主键不更新
            if ($key == $primary_key) {
                $where = '`'. $key . '`=?';
                $where_value = $value;
                continue;
            }
            if ($value === null) {
                continue;
            } else {
                $fields[] = '`'. $key . '`=?';
                $field_values[] = $value;
            }
        }

        if ($where == null) {
            $this->set_error('更新数据时未指定条件！');
            return false;
        }

        $sql = 'UPDATE `' . $table . '` SET ' . implode(',', $fields) . ' WHERE ' . $where;
        $field_values[] = $where_value;

        return $this->execute($sql, $field_values);
    }

    /**
     * 获取一个表的字段列表
     *
     * @param string $table 表名
     * @return array
     */
    public function get_table_fields($table)
    {
        $fields = $this->get_objects('SHOW FIELDS FROM `' . $table . '`');

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
    public function drop_table($table)
    {
        return $this->execute('DROP TABLE IF EXISTS `' . $table .'`');
    }

}