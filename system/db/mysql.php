<?php

namespace system\db;

use \system\be;
use \system\cache;

/**
 * 数据库类
 */
class mysql
{
    private static $errors = array(); // 保存错误信息

    private static $connection = null; // 数据库连接

    private static $statement = null; // 预编译 sql

    /**
     * 连接数据库
     *
     * @return bool 是否连接成功
     * @throws
     */
    public static function connect()
    {
        $config = be::get_config('db');
        $connection = new \PDO('mysql:dbname=' . $config->name . ';host=' . $config->host . ';port=' . $config->port . ';charset=utf8', $config->user, $config->pass);
        if (!$connection) throw new exception('连接 数据库' . $config->name . '（' . $config->host . '） 失败！');

        // 设置默认编码为 UTF-8 ，UTF-8 为 PHPBE 默认标准字符集编码
        $connection->query('SET NAMES utf8');

        self::$connection = $connection;
        return true;
    }

    /**
     * 关闭数据库连接
     *
     * @return bool 是否关闭成功
     */
    public static function close()
    {
        if (self::$connection) self::$connection = null;
        return true;
    }

    /**
     * 执行 sql 语句
     *
     * @param string $sql 查询语句
     * @return \PDOStatement SQL预编译结果对象
     * @throws
     */
    public static function prepare($sql, array $driver_options = array())
    {
        if (!isset(self::$connection)) self::connect();

        $statement = self::$connection->prepare($sql, $driver_options);
        if (!$statement) {
            throw new exception($statement->errorCode() . '：' . $statement->errorInfo() . ' SQL=' . $sql);
        }

        self::$statement = $statement;
        return self::$statement;
    }

    /**
     * 执行 sql 语句
     *
     * @param string $sql 查询语句
     * @param array $bind 占位参数
     * @return bool 执行成功/执行失败
     * @throws
     */
    public static function execute($sql = null, $bind = array())
    {
        if ($sql === null) {
            if (self::$statement == null) {
                self::set_error('没有预编译SQL！');
                return false;
            }

            if (!self::$statement->execute($bind)) {
                $error = self::$statement->errorInfo();
                //print_r($error);
                throw new exception($error[1] . '：' . $error[2]);
            }

            return true;
        } else {
            self::free();

            if (count($bind) > 0) {
                self::prepare($sql);
                return self::execute(null, $bind);
            } else {
                if (!isset(self::$connection)) self::connect();
                if (!isset(self::$connection)) return false;

                $statement = self::$connection->query($sql);
                if ($statement === false) {
                    $error = self::$connection->errorInfo();
                    // print_r($error);
                    throw new exception($error[1] . '：' . $error[2] . ' SQL=' . $sql);
                }
                self::$statement = $statement;

                return true;
            }
        }
    }

    /**
     * 释放查询结果
     *
     * @return \PDOStatement
     */
    public static function get_statement()
    {
        return self::$statement;
    }

    /**
     * 释放查询结果
     *
     * @return bool 是否释放成功
     */
    public static function free()
    {
        if (self::$statement) self::$statement->closeCursor();
        self::$statement = null;
        return true;
    }

    /**
     * 最后一次查询影响到的记录条数
     * @return int | bool 条数/失败
     */
    public static function row_count()
    {
        if (self::$statement == null) {
            self::set_error('没有预编译SQL！');
            return false;
        }
        return self::$statement->rowCount();
    }

    /**
     * 返回单一查询结果, 多行多列记录时, 只返回第一行第一列
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param int $cache_expire 缓存失效时间（单位：秒），等于 0 时不使用缓存，
     * @return string | false
     */
    public static function get_result($sql = null, $bind = array(), $cache_expire = 0)
    {
        $cache_key = null;
        if ($cache_expire > 0) {
            $cache_key = 'mysql:get_result:' . sha1($sql . serialize($bind));
            $cache = cache::get($cache_key);
            if ($cache !== false) return $cache;
        }

        $result = false;
        if (self::execute($sql, $bind)) {
            $row = self::$statement->fetch(\PDO::FETCH_NUM);
            if ($row) $result = $row[0];
        }

        if ($cache_expire > 0) {
            cache::set($cache_key, $result, $cache_expire);
        }

        return $result;
    }

    /**
     * 返回查询单列结果的数组。$index:  取第几列
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param int $cache_expire 缓存失效时间（单位：秒），等于 0 时不使用缓存，
     * @return array | false
     */
    public static function get_results($sql = null, $bind = array(), $cache_expire = 0)
    {
        $cache_key = null;
        if ($cache_expire > 0) {
            $cache_key = 'mysql:get_results:' . sha1($sql . serialize($bind));
            $cache = cache::get($cache_key);
            if ($cache !== false) return $cache;
        }

        $result = false;
        if (self::execute($sql, $bind)) {
            $result = self::$statement->fetchAll(\PDO::FETCH_COLUMN, 0);
        }

        if ($cache_expire > 0) {
            cache::set($cache_key, $result, $cache_expire);
        }

        return $result;
    }

    /**
     * 返回一个数据库记录对象
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param int $cache_expire 缓存失效时间（单位：秒），等于 0 时不使用缓存，
     * @return object | false
     */
    public static function get_object($sql = null, $bind = array(), $cache_expire = 0)
    {
        $cache_key = null;
        if ($cache_expire > 0) {
            $cache_key = 'mysql:get_object:' . sha1($sql . serialize($bind));
            $cache = cache::get($cache_key);
            if ($cache !== false) return $cache;
        }

        $result = false;
        if (self::execute($sql, $bind)) {
            $result = self::$statement->fetchObject();
        }

        if ($cache_expire > 0) {
            cache::set($cache_key, $result, $cache_expire);
        }

        return $result;
    }

    /**
     * 返回一个对象数组，如果设置了 $key, 该数组按该 key 生成索引下标。
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param int $cache_expire 缓存失效时间（单位：秒），等于 0 时不使用缓存，
     * @return array(object) | false
     */
    public static function get_objects($sql = null, $bind = array(), $cache_expire = 0)
    {
        $cache_key = null;
        if ($cache_expire > 0) {
            $cache_key = 'mysql:get_objects:' . sha1($sql . serialize($bind));
            $cache = cache::get($cache_key);
            if ($cache !== false) return $cache;
        }

        $result = false;
        if (self::execute($sql, $bind)) {
            $result = self::$statement->fetchAll(\PDO::FETCH_OBJ);
        }

        if ($cache_expire > 0) {
            cache::set($cache_key, $result, $cache_expire);
        }

        return $result;
    }

    /**
     * 返回一个数组，如果设置了 $key, 该数组按该 key 生成索引下标。
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param int $cache_expire 缓存失效时间（单位：秒），等于 0 时不使用缓存，
     * @return array | false
     */
    public static function get_array($sql = null, $bind = array(), $cache_expire = 0)
    {
        $cache_key = null;
        if ($cache_expire > 0) {
            $cache_key = 'mysql:get_array:' . sha1($sql . serialize($bind));
            $cache = cache::get($cache_key);
            if ($cache !== false) return $cache;
        }

        $result = false;
        if (self::execute($sql, $bind)) {
            $result = self::$statement->fetch(\PDO::FETCH_ASSOC);
        }

        if ($cache_expire > 0) {
            cache::set($cache_key, $result, $cache_expire);
        }

        return $result;
    }

    /**
     * 返回一个二维数组，如果设置了 $key, 该数组按该 key 生成索引下标。
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param int $cache_expire 缓存失效时间（单位：秒），等于 0 时不使用缓存，
     * @return array | false
     */
    public static function get_arrays($sql = null, $bind = array(), $cache_expire = 0)
    {
        $cache_key = null;
        if ($cache_expire > 0) {
            $cache_key = 'mysql:get_arrays:' . sha1($sql . serialize($bind));
            $cache = cache::get($cache_key);
            if ($cache !== false) return $cache;
        }

        $result = false;
        if (self::execute($sql, $bind)) {
            $result = self::$statement->fetchAll(\PDO::FETCH_ASSOC);
        }

        if ($cache_expire > 0) {
            cache::set($cache_key, $result, $cache_expire);
        }

        return $result;
    }

    /**
     * 插入一个对象到数据库
     *
     * @param string $table 表名
     * @param object /array(object) $obj 要插入数据库的对象或对象数组，对象属性需要和该表字段一致
     * @return bool
     */
    public static function insert($table, $obj)
    {
        // 批量插入
        if (is_array($obj)) {
            $vars = get_object_vars($obj[0]);
            $sql = 'INSERT INTO `' . $table . '`(`' . implode('`,`', array_keys($vars)) . '`) VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
            self::prepare($sql);
            foreach ($obj as $o) {
                $vars = get_object_vars($o);
                self::execute(null, array_values($vars));
            }
            return true;
        } else {
            $vars = get_object_vars($obj);
            $sql = 'INSERT INTO `' . $table . '`(`' . implode('`,`', array_keys($vars)) . '`) VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
            if (!self::execute($sql, array_values($vars))) return false;
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
    public static function update($table, $obj, $primary_key)
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
            self::set_error('更新数据时未指定条件！');
            return false;
        }

        $sql = 'UPDATE `' . $table . '` SET `' . implode('`,`', $fields) . '` WHERE ' . $where;
        $field_values[] = $where_value;

        return self::execute($sql, $field_values);
    }

    /**
     * 处理字符串防止 SQL 注入
     *
     * @param string $string 字符串
     * @return string
     */
    public static function quote($string)
    {
        if (!isset(self::$connection)) self::connect();
        if (!isset(self::$connection)) return $string;
        return self::$connection->quote($string);
    }

    /**
     * 获取 insert 插入后产生的 id
     *
     * @return int
     */
    public static function get_insert_id()
    {
        return self::get_last_insert_id();
    }

    public static function get_last_insert_id()
    {
        if (!isset(self::$connection)) self::connect();
        if (!isset(self::$connection)) return false;
        return self::$connection->lastInsertId();
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public static function get_tables()
    {
        return self::get_objects('SHOW TABLES');
    }

    /**
     * 获取一个表的字段列表
     *
     * @param string $table 表名
     * @return array
     */
    public static function get_table_fields($table)
    {
        $fields = self::get_objects('SHOW FIELDS FROM `' . $table . '`');

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
        return self::execute('DROP TABLE IF EXISTS `' . $table .'`');
    }

    /**
     * 开启事务处理
     *
     * @return bool
     */
    public function start_transaction()
    {
        return self::begin_transaction();
    }

    public function begin_transaction()
    {
        if (!isset(self::$connection)) self::connect();
        if (!isset(self::$connection)) return false;
        return self::$connection->beginTransaction();
    }

    /**
     * 事务回滚
     *
     * @return bool
     */
    public function rollback()
    {
        if (!isset(self::$connection)) self::connect();
        if (!isset(self::$connection)) return false;
        return self::$connection->rollBack();
    }

    /**
     * 事务提交
     *
     * @return bool
     */
    public function commit()
    {
        if (!isset(self::$connection)) self::connect();
        if (!isset(self::$connection)) return false;
        return self::$connection->commit();
    }

    /**
     * 是否在事务中
     *
     * @return bool
     */
    public function in_transaction()
    {
        if (!isset(self::$connection)) self::connect();
        if (!isset(self::$connection)) return false;
        return self::$connection->inTransaction();
    }

    /**
     * 获取数据库连接对象
     *
     * @return resource
     */
    public static function get_connection()
    {
        if (!isset(self::$connection)) self::connect();
        return self::$connection;
    }

    /**
     * 获取 版本号
     *
     * @return string
     */
    public static function get_version()
    {
        if (!isset(self::$connection)) self::connect();
        if (!isset(self::$connection)) return '';
        return self::$connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    public static function set_error($error)
    {
        self::$errors[] = $error;
    }

    public static function get_error()
    {
        if (count(self::$errors) > 0) {
            return self::$errors[0];
        }
        return false;
    }

    public function get_errors()
    {
        return self::$errors;
    }

    public static function has_error()
    {
        return count(self::$errors) > 0 ? true : false;
    }

    public static function clear_errors()
    {
        self::$errors = array();
    }
}