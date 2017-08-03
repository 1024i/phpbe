<?php

namespace system\db;

use system\be;

/**
 * 数据库类
 */
class driver
{
    private $errors = array(); // 保存错误信息

    /**
     * @var \PDO
     */
    private $connection = null; // 数据库连接

    /**
     * @var \PDOStatement
     */
    private $statement = null; // 预编译 sql

    /**
     * 连接数据库
     *
     * @return bool 是否连接成功
     * @throws
     */
    public function connect()
    {
        $config = be::get_config('db');
        $connection = new \PDO('mysql:dbname=' . $config->name . ';host=' . $config->host . ';port=' . $config->port . ';charset=utf8', $config->user, $config->pass);
        if (!$connection) throw new exception('连接 数据库' . $config->name . '（' . $config->host . '） 失败！');

        // 设置默认编码为 UTF-8 ，UTF-8 为 PHPBE 默认标准字符集编码
        $connection->query('SET NAMES utf8');

        $this->connection = $connection;
        return true;
    }

    /**
     * 关闭数据库连接
     *
     * @return bool 是否关闭成功
     */
    public function close()
    {
        if ($this->connection) $this->connection = null;
        return true;
    }

    /**
     * 执行 sql 语句
     *
     * @param string $sql 查询语句
     * @return \PDOStatement SQL预编译结果对象
     * @throws
     */
    public function prepare($sql, array $driver_options = array())
    {
        if (!isset($this->connection)) $this->connect();

        $statement = $this->connection->prepare($sql, $driver_options);
        if (!$statement) {
            throw new exception($statement->errorCode() . '：' . $statement->errorInfo() . ' SQL=' . $sql);
        }

        $this->statement = $statement;
        return $this->statement;
    }

    /**
     * 执行 sql 语句
     *
     * @param string $sql 查询语句
     * @param array $bind 占位参数
     * @return bool 执行成功/执行失败
     * @throws
     */
    public function execute($sql = null, $bind = array())
    {
        if ($sql === null) {
            if ($this->statement == null) {
                $this->set_error('没有预编译SQL！');
                return false;
            }

            if (!$this->statement->execute($bind)) {
                $error = $this->statement->errorInfo();
                //print_r($error);
                throw new exception($error[1] . '：' . $error[2]);
            }

            return true;
        } else {
            $this->free();

            if (count($bind) > 0) {
                $this->prepare($sql);
                return $this->execute(null, $bind);
            } else {
                if (!isset($this->connection)) $this->connect();
                if (!isset($this->connection)) return false;

                $statement = $this->connection->query($sql);
                if ($statement === false) {
                    $error = $this->connection->errorInfo();
                    // print_r($error);
                    throw new exception($error[1] . '：' . $error[2] . ' SQL=' . $sql);
                }
                $this->statement = $statement;

                return true;
            }
        }
    }

    /**
     * 释放查询结果
     *
     * @return \PDOStatement
     */
    public function get_statement()
    {
        return $this->statement;
    }

    /**
     * 释放查询结果
     *
     * @return bool 是否释放成功
     */
    public function free()
    {
        if ($this->statement) $this->statement->closeCursor();
        $this->statement = null;
        return true;
    }

    /**
     * 最后一次查询影响到的记录条数
     * @return int | bool 条数/失败
     */
    public function row_count()
    {
        if ($this->statement == null) {
            $this->set_error('没有预编译SQL！');
            return false;
        }
        return $this->statement->rowCount();
    }

    /**
     * 返回单一查询结果, 多行多列记录时, 只返回第一行第一列
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return string | false
     */
    public function get_value($sql = null, $bind = array())
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $row = $this->statement->fetch(\PDO::FETCH_NUM);
            if ($row) $result = $row[0];
        }

        return $result;
    }

    /**
     * 返回查询单列结果的数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array | false
     */
    public function get_values($sql = null, $bind = array())
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $result = $this->statement->fetchAll(\PDO::FETCH_COLUMN);
        }

        return $result;
    }

    /**
     * 返回一个跌代器数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return void
     */
    public function get_yield_values($sql = null, $bind = array())
    {
        if ($this->execute($sql, $bind)) {
            while ($row = $this->statement->fetch(\PDO::FETCH_NUM)) {
                yield $row[0];
            }
        }
    }

    /**
     * 返回键值对数组
     * 查询两个或两个以上字段，第一列字段作为 key, 乘二列字段作为 value，多于两个字段时忽略
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array | false
     */
    public function get_key_values($sql = null, $bind = array())
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $result = $this->statement->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
        }

        return $result;
    }

    /**
     * 返回一个数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array | false
     */
    public function get_array($sql = null, $bind = array())
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $result = $this->statement->fetch(\PDO::FETCH_ASSOC);
        }

        return $result;
    }

    /**
     * 返回一个二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array | false
     */
    public function get_arrays($sql = null, $bind = array())
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $result;
    }

    /**
     * 返回一个跌代器二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return void
     */
    public function get_yield_arrays($sql = null, $bind = array())
    {
        if ($this->execute($sql, $bind)) {
            while ($result = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
                yield $result;
            }
        }
    }

    /**
     * 返回一个带下标索引的二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param string $key 作为下标索引的字段名
     * @return array | false
     */
    public function get_key_arrays($sql = null, $bind = array(), $key)
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $arrays = $this->statement->fetchAll(\PDO::FETCH_ASSOC);

            $result = [];
            foreach ($arrays as $array) {
                $result[$array[$key]] = $array;
            }
        }

        return $result;
    }

    /**
     * 返回一个数据库记录对象
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return object | false
     */
    public function get_object($sql = null, $bind = array())
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $result = $this->statement->fetchObject();
        }

        return $result;
    }

    /**
     * 返回一个对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array(object) | false
     */
    public function get_objects($sql = null, $bind = array())
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $result = $this->statement->fetchAll(\PDO::FETCH_OBJ);
        }

        return $result;
    }

    /**
     * 返回一个跌代器对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return void
     */
    public function get_yield_objects($sql = null, $bind = array())
    {
        if ($this->execute($sql, $bind)) {
            while ($result = $this->statement->fetchObject()) {
                yield $result;
            }
        }
    }

    /**
     * 返回一个带下标索引的对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @param string $key 作为下标索引的字段名
     * @return array(object) | false
     */
    public function get_key_objects($sql = null, $bind = array(), $key)
    {
        $result = false;
        if ($this->execute($sql, $bind)) {
            $objects = $this->statement->fetchAll(\PDO::FETCH_OBJ);
            $result = [];
            foreach ($objects as $object) {
                $result[$object->$key] = $object;
            }
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

        $sql = 'UPDATE `' . $table . '` SET `' . implode('`,`', $fields) . '` WHERE ' . $where;
        $field_values[] = $where_value;

        return $this->execute($sql, $field_values);
    }

    /**
     * 处理字符串防止 SQL 注入
     *
     * @param string $string 字符串
     * @return string
     */
    public function quote($string)
    {
        if (!isset($this->connection)) $this->connect();
        if (!isset($this->connection)) return $string;
        return $this->connection->quote($string);
    }

    /**
     * 获取 insert 插入后产生的 id
     *
     * @return int
     */
    public function get_insert_id()
    {
        return $this->get_last_insert_id();
    }

    public function get_last_insert_id()
    {
        if (!isset($this->connection)) $this->connect();
        if (!isset($this->connection)) return false;
        return $this->connection->lastInsertId();
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public function get_tables()
    {
        return $this->get_objects('SHOW TABLES');
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

    /**
     * 开启事务处理
     *
     * @return bool
     */
    public function start_transaction()
    {
        return $this->begin_transaction();
    }

    public function begin_transaction()
    {
        if (!isset($this->connection)) $this->connect();
        if (!isset($this->connection)) return false;
        return $this->connection->beginTransaction();
    }

    /**
     * 事务回滚
     *
     * @return bool
     */
    public function rollback()
    {
        if (!isset($this->connection)) $this->connect();
        if (!isset($this->connection)) return false;
        return $this->connection->rollBack();
    }

    /**
     * 事务提交
     *
     * @return bool
     */
    public function commit()
    {
        if (!isset($this->connection)) $this->connect();
        if (!isset($this->connection)) return false;
        return $this->connection->commit();
    }

    /**
     * 是否在事务中
     *
     * @return bool
     */
    public function in_transaction()
    {
        if (!isset($this->connection)) $this->connect();
        if (!isset($this->connection)) return false;
        return $this->connection->inTransaction();
    }

    /**
     * 获取数据库连接对象
     *
     * @return \PDO
     */
    public function get_connection()
    {
        if (!isset($this->connection)) $this->connect();
        return $this->connection;
    }

    /**
     * 获取 版本号
     *
     * @return string
     */
    public function get_version()
    {
        if (!isset($this->connection)) $this->connect();
        if (!isset($this->connection)) return '';
        return $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    public function set_error($error)
    {
        $this->errors[] = $error;
    }

    public function get_error()
    {
        if (count($this->errors) > 0) {
            return $this->errors[0];
        }
        return false;
    }

    public function get_errors()
    {
        return $this->errors;
    }

    public function has_error()
    {
        return count($this->errors) > 0 ? true : false;
    }

    public function clear_errors()
    {
        $this->errors = array();
    }
}