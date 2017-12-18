<?php
namespace system\db;

/**
 * 数据库类
 */
class driver
{
    /**
     * @var \PDO
     */
    protected $connection = null; // 数据库连接

    /**
     * @var \PDOStatement
     */
    protected $statement = null; // 预编译 sql

    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 连接数据库
     *
     * @return bool 是否连接成功
     * @throws
     */
    public function connect()
    {
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
    public function prepare($sql, array $driver_options = [])
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
     * @return true 执行成功
     * @throws exception
     */
    public function execute($sql = null, $bind = [])
    {
        if ($sql === null) {
            if ($this->statement == null) {
                throw new exception('没有预编译SQL！');
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
     * @throws exception
     */
    public function row_count()
    {
        if ($this->statement == null) {
            throw new exception('没有预编译SQL！');
        }
        return $this->statement->rowCount();
    }

    /**
     * 返回单一查询结果, 多行多列记录时, 只返回第一行第一列
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return string
     */
    public function get_value($sql = null, $bind = [])
    {
        $this->execute($sql, $bind);
        $row = $this->statement->fetch(\PDO::FETCH_NUM);
        return $row[0];
    }

    /**
     * 返回查询单列结果的数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array
     */
    public function get_values($sql = null, $bind = [])
    {
        $this->execute($sql, $bind);
        return $this->statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * 返回一个跌代器数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return void
     */
    public function get_yield_values($sql = null, $bind = [])
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
     * @return array
     */
    public function get_key_values($sql = null, $bind = [])
    {
        $this->execute($sql, $bind);
        return $this->statement->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
    }

    /**
     * 返回一个数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array
     */
    public function get_array($sql = null, $bind = [])
    {
        $this->execute($sql, $bind);
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 返回一个二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array
     */
    public function get_arrays($sql = null, $bind = [])
    {
        $this->execute($sql, $bind);
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 返回一个跌代器二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return void
     */
    public function get_yield_arrays($sql = null, $bind = [])
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
     * @return array
     */
    public function get_key_arrays($sql = null, $bind = [], $key)
    {
        $this->execute($sql, $bind);
        $arrays = $this->statement->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($arrays as $array) {
            $result[$array[$key]] = $array;
        }

        return $result;
    }

    /**
     * 返回一个数据库记录对象
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return object
     */
    public function get_object($sql = null, $bind = [])
    {
        $this->execute($sql, $bind);
        return $this->statement->fetchObject();
    }

    /**
     * 返回一个对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array(object)
     */
    public function get_objects($sql = null, $bind = [])
    {
        $this->execute($sql, $bind);
        return $this->statement->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * 返回一个跌代器对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return void
     */
    public function get_yield_objects($sql = null, $bind = [])
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
     * @return array(object)
     */
    public function get_key_objects($sql = null, $bind = [], $key)
    {
        $this->execute($sql, $bind);
        $objects = $this->statement->fetchAll(\PDO::FETCH_OBJ);
        $result = [];
        foreach ($objects as $object) {
            $result[$object->$key] = $object;
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
            $sql = 'INSERT INTO ' . $table . '(' . implode(',', array_keys($vars)) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
            $this->prepare($sql);
            foreach ($obj as $o) {
                $vars = get_object_vars($o);
                $this->execute(null, array_values($vars));
            }
        } else {
            $vars = get_object_vars($obj);
            $sql = 'INSERT INTO ' . $table . '(' . implode(',', array_keys($vars)) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
            $this->execute($sql, array_values($vars));
        }

        return true;
    }

    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param object $obj 要插入数据库的对象，对象属性需要和该表字段一致
     * @param string $primary_key 主键
     * @return bool
     * @throws exception
     */
    public function update($table, $obj, $primary_key)
    {
        $fields = [];
        $field_values = [];

        $where = null;
        $where_value = null;

        foreach (get_object_vars($obj) as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            // 主键不更新
            if ($key == $primary_key) {
                $where = ''. $key . '=?';
                $where_value = $value;
                continue;
            }
            if ($value === null) {
                continue;
            } else {
                $fields[] = ''. $key . '=?';
                $field_values[] = $value;
            }
        }

        if ($where == null) {
            throw new exception('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $table . ' SET ' . implode(',', $fields) . ' WHERE ' . $where;
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
        $fields = $this->get_objects('SHOW FIELDS FROM ' . $table);

        $data = [];
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
        return $this->execute('DROP TABLE IF EXISTS ' . $table);
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
}