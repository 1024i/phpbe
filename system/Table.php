<?php
namespace system;

use System\Db\Exception;

/**
 * 数据库表 查询器
 */
class Table extends Obj
{
    protected $db = 'master';

    protected $tableName = ''; // 表名
    protected $primaryKey = 'id'; // 主键
    protected $fields = []; // 字段列表

    protected $quote = '`'; // 字段或表名转义符 mysql: `

    protected $alias = ''; // 当前表的别名
    protected $join = array(); // 表连接
    protected $where = array(); // where 条件
    protected $groupBy = ''; // 分组
    protected $having = ''; // having
    protected $offset = 0; // 分页编移
    protected $limit = 0; // 分页大小
    protected $orderBy = ''; // 排序

    protected $lastSql = null; // 上次执行的 SQL

    /**
     * 缓存失效时间（单位：秒），0 为不使用缓存
     */
    protected $cacheExpire = 0;

    /**
     * 切换库
     *
     * @param string $db db配置文件中的库名
     * @return table
     */
    public function db($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * 切换表名
     *
     * @param string $tableName 表名
     * @return table
     */
    public function table($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * 给当前表设置别名
     *
     * @param string $alias 别名
     * @return table
     */
    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }


    /**
     * 左连接
     *
     * @param string $table 表名
     * @param string $on 连接条件
     * @return table
     */
    public function leftJoin($table, $on)
    {
        $this->join[] = array('LEFT JOIN', $table, $on);
        return $this;
    }

    /**
     * 右连接
     *
     * @param string $table 表名
     * @param string $on 连接条件
     * @return table
     */
    public function rightJoin($table, $on)
    {
        $this->join[] = array('RIGHT JOIN', $table, $on);
        return $this;
    }

    /**
     * 内连接
     *
     * @param string $table 表名
     * @param string $on 连接条件
     * @return table
     */
    public function innerJoin($table, $on)
    {
        $this->join[] = array('INNER JOIN', $table, $on);
        return $this;
    }

    /**
     * 内连接 同 innerJoin
     *
     * @param string $table 表名
     * @param string $on 连接条件
     * @return table
     */
    public function join($table, $on)
    {
        $this->join[] = array('INNER JOIN', $table, $on);
        return $this;
    }

    /**
     * 全连接
     *
     * @param string $table 表名
     * @param string $on 连接条件
     * @return table
     */
    public function fullJoin($table, $on)
    {
        $this->join[] = array('FULL JOIN', $table, $on);
        return $this;
    }

    /**
     * 交叉连接
     *
     * @param string $table 表名
     * @param string $on 连接条件
     * @return table
     */
    public function crossJoin($table, $on)
    {
        $this->join[] = array('CROSS JOIN', $table, $on);
        return $this;
    }

    /**
     * 设置查询条件
     *
     * @param string | array $field 字段名或需要直接拼接进SQL的字符
     * @param string $op 操作类型：=/<>/!=/>/</>=/<=/between/not between/in/not in/like/not like
     * @param string $value 值，
     * @return table
     * @example
     * <pre>
     * $table->where('username','Tom');
     * $table->where('username','like','Tom');
     * $table->where('age','=',18);
     * $table->where('age','>',18);
     * $table->where('age','between', array(18, 30));
     * $table->where('userId','in', array(1, 2, 3, 4));
     * $table->where('username LIKE \'Tom\'');
     * $table->where('username LIKE ?', array('Tom'));
     * $table->where('(')->where('username','like','Tom')->where('OR')->where('age','>',18)->where(')');
     * $table->where(array(
     *     array('username','Tom'),
     *     'OR',
     *     array('age','>',18),
     *)); // 最终SQL: WHERE (username='Tom' OR age>18)
     * </pre>
     */
    public function where($field, $op = null, $value = null)
    {
        $n = count($this->where);

        // 如果第一个参数为数组，认定为一次传入多个条件
        if (is_array($field)) {

            if (count($field) == 0) return $this;

            if ($n > 0 && (is_array($this->where[$n - 1]) || substr($this->where[$n - 1], -1) == ')')) {
                $this->where[] = 'AND';
            }

            $this->where[] = '(';
            foreach ($field as $w) {
                if (is_array($w)) {
                    $len = count($w);
                    if (is_array($w[0]) || $len > 3 || ($len == 3 && is_array($w[1]))) {
                        $this->where($w);
                    } else {
                        if ($len == 2) {
                            $this->where($w[0], $w[1]);
                        } elseif ($len == 3) {
                            $this->where($w[0], $w[1], $w[2]);
                        }
                    }
                } else {
                    $this->where[] = $w;
                }
            }
            $this->where[] = ')';
        } else {

            $field = trim($field);

            if ($op === null) {
                if (substr($field, 0, 1) == '(') {
                    if ( $n > 0 && (is_array($this->where[$n - 1]) || substr($this->where[$n - 1], -1) == ')')) {
                        $this->where[] = 'AND';
                    }
                }
            } else {
                if ($n > 0 && (is_array($this->where[$n - 1]) || substr($this->where[$n - 1], -1) == ')')) {
                    $this->where[] = 'AND';
                }
            }

            if ($op === null) {  // 第二个参数为空时，第一个参数直接拼入 sql
                $this->where[] = $field;
            } elseif (is_array($op)) { // 第二个参数为数组时，传入的为带占位符的 sql
                $this->where[] = array($field, $op);
            } elseif ($value === null) {
                $this->where[] = array($field, '=', $op); // 等值查询
            } else {
                $this->where[] = array($field, $op, $value); // 普通条件查询
            }
        }

        return $this;
    }

    /**
     * 分组
     *
     * @param string $field 分组条件
     * @return table
     */
    public function groupBy($field)
    {
        $this->groupBy = $field;
        return $this;
    }

    /**
     * Having 筛选
     *
     * @param string $having
     * @return table
     */
    public function having($having)
    {
        $this->having = $having;
        return $this;
    }

    /**
     * 偏移量
     *
     * @param int $offset 偏移量
     * @return table
     */
    public function offset($offset = 0)
    {
        $this->offset = intval($offset);
        return $this;
    }

    /**
     * 最多返回多少条记录
     *
     * @param int $limit 要返回的记录条数
     * @return table
     */
    public function limit($limit = 20)
    {
        $this->limit = intval($limit);
        return $this;
    }

    /**
     * 排序
     *
     * @param string $field 要排序的字段
     * @param string $dir 排序方向：ASC | DESC
     * @return table
     */
    public function orderBy($field, $dir = null)
    {
        $field = trim($field);
        if ($dir == null) {
            $this->orderBy = $field;
        } else {
            $dir = strtoupper(trim($dir));
            if ($dir != 'ASC' && $dir != 'DESC') {
                $this->orderBy = $field;
            } else {
                $this->orderBy = $this->quote . $field . $this->quote . ' ' . $dir;
            }
        }
        return $this;
    }

    /**
     * 缓存查询结果
     *
     * @param int $expire 缓存有期时间（单位：秒）
     * @return table
     */
    public function cache($expire = 60)
    {
        $this->cacheExpire = intval($expire);
        return $this;
    }

    /**
     * 查询单个字段第一条记录
     *
     * @param string $field 查询的字段
     * @return string|int
     */
    public function getValue($field)
    {
        return $this->query('getValue', $field);
    }

    /**
     * 查询单个字段的所有记录
     *
     * @param string $field 查询的字段
     * @return array 数组
     */
    public function getValues($field)
    {
        return $this->query('getValues', $field);
    }

    /**
     * 查询单个字段的所有记录, 跌代器方式
     *
     * @param string $field 查询的字段
     * @return array
     */
    public function getYieldValues($field)
    {
        return $this->query('getYieldValues', $field);
    }

    /**
     * 查询键值对
     *
     * @param string $keyField 键字段
     * @param string $valueField 值字段
     * @return array 数组
     */
    public function getKeyValues($keyField, $valueField)
    {
        return $this->query('getKeyValues', $keyField.','.$valueField);
    }

    /**
     * 查询单条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return array 数组
     */
    public function getArray($fields = null)
    {
        return $this->query('getArray', $fields);
    }

    /**
     * 查询多条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return array 二维数组
     */
    public function getArrays($fields = null)
    {
        return $this->query('getArrays', $fields);
    }

    /**
     * 查询多条记录, 跌代器方式
     *
     * @param string $fields 查询用到的字段列表
     * @return array
     */
    public function getYieldArrays($fields = null)
    {
        return $this->query('getYieldArrays', $fields);
    }

    /**
     * 查询多条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return array 二维数组
     */
    public function getKeyArrays($keyField, $fields = null)
    {
        return $this->query('getKeyArrays', $fields, $keyField);
    }

    /**
     * 查询单条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return object 对象
     */
    public function getObject($fields = null)
    {
        return $this->query('getObject', $fields);
    }

    /**
     * 查询多条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return array
     */
    public function getObjects($fields = null)
    {
        return $this->query('getObjects', $fields);
    }

    /**
     * 查询多条记录, 跌代器方式
     *
     * @param string $fields 查询用到的字段列表
     * @return array
     */
    public function getYieldObjects($fields = null)
    {
        return $this->query('getYieldObjects', $fields);
    }

    /**
     * 查询多条记发
     *
     * @param string $fields 查询用到的字段列表
     * @return array 对象列表
     */
    public function getKeyObjects($keyField, $fields = null)
    {
        return $this->query('getKeyObjects', $fields, $keyField);
    }

    /**
     * 执行数据库查询
     *
     * @param string $fn 指定数据库查询函数名
     * @param string $fields 查询用到的字段列表
     * @return mixed
     */
    private function query($fn, $fields = null, $keyField = null)
    {
        $sqlData = $this->prepareSql();
        $sql = null;
        if ($fields === null) {
            $sql = 'SELECT ' . $this->quote . implode($this->quote . ',' . $this->quote, $this->fields) . $this->quote;
        } else {
            $sql = 'SELECT ' . $fields;
        }

        $sql .= ' FROM ' . $this->quote . $this->tableName . $this->quote;
        if ($this->alias) {
            $sql .= ' AS ' . $this->alias;
        }
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= $sqlData[0];

        $this->lastSql = array($sql, $sqlData[1]);

        $cacheKey = null;
        if ($this->cacheExpire > 0) {
            $cacheKey = 'table:' . $fn . ':' . sha1($sql . serialize($sqlData[1]));
            $cache = cache::get($cacheKey);
            if ($cache !== false) return $cache;
        }

        $db = Be::getDb($this->db);
        $result = $keyField === null ? $db->$fn($sql, $sqlData[1]) : $db->$fn($sql, $sqlData[1], $keyField);

        if ($this->cacheExpire > 0) {
            cache::set($cacheKey, $result, $this->cacheExpire);
        }

        return $result;
    }

    /**
     * 纺计数量
     *
     * @param string $field 字段
     * @return int
     */
    public function count($field = '*')
    {
        return $this->query('getValue', 'COUNT(' . $field . ')');
    }

    /**
     * 求和
     *
     * @param string $field 字段名
     * @return number
     */
    public function sum($field)
    {
        return $this->query('getValue', 'SUM(' . $field . ')');
    }

    /**
     * 取最小值
     *
     * @param string $field 字段名
     * @return number
     */
    public function min($field)
    {
        return $this->query('getValue', 'MIN(' . $field . ')');
    }

    /**
     * 取最大值
     *
     * @param string $field 字段名
     * @return number
     */
    public function max($field)
    {
        return $this->query('getValue', 'MAX(' . $field . ')');
    }

    /**
     * 取平均值
     *
     * @param string $field 字段名
     * @return number
     */
    public function avg($field)
    {
        return $this->query('getValue', 'AVG(' . $field . ')');
    }

    /**
     * 自增某个字段
     *
     * @param string $field 字段名
     * @param int $step 自增量     *
     * @return bool
     */
    public function increment($field, $step = 1)
    {
        $sqlData = $this->prepareSql();
        $sql = 'UPDATE ' . $this->quote . $this->tableName . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '+' . intval($step);
        $sql .= $sqlData[0];
        $this->lastSql = array($sql, $sqlData[1]);

        $db = Be::getDb($this->db);
        $db->execute($sql, $sqlData[1]);

        return true;
    }

    /**
     * 自减某个字段
     *
     * @param string $field 字段名
     * @param int $step 自减量    *
     * @return bool
     */
    public function decrement($field, $step = 1)
    {
        $sqlData = $this->prepareSql();
        $sql = 'UPDATE ' . $this->quote . $this->tableName . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '-' . intval($step);
        $sql .= $sqlData[0];
        $this->lastSql = array($sql, $sqlData[1]);

        $db = Be::getDb($this->db);
        $db->execute($sql, $sqlData[1]);

        return true;
    }

    /**
     * 更新数据
     *
     * @param array $values 要更新的数据键值对     *
     * @return bool
     */
    public function update($values = array())
    {
        $sqlData = $this->prepareSql();

        $sql = 'UPDATE ' . $this->quote . $this->tableName . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' SET ' . $this->quote . implode($this->quote . '=?,' . $this->quote, array_keys($values)) . $this->quote . '=?';
        $sql .= $sqlData[0];
        $this->lastSql = array($sql, $sqlData[1]);

        $db = Be::getDb($this->db);
        $db->execute($sql, array_merge(array_values($values), $sqlData[1]));

        return true;
    }

    /**
     * 删除数据
     *
     * @return bool
     */
    public function delete()
    {
        $sqlData = $this->prepareSql();
        $sql = 'DELETE FROM ' . $this->quote . $this->tableName . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= $sqlData[0];
        $this->lastSql = array($sql, $sqlData[1]);

        $db = Be::getDb($this->db);
        $db->execute($sql, $sqlData[1]);

        return true;
    }

    /**
     * 清空表
     *
     * @return bool
     */
    public function truncate()
    {
        $sql = 'TRUNCATE TABLE ' . $this->quote . $this->tableName . $this->quote;
        $this->lastSql = array($sql, []);

        $db = Be::getDb($this->db);
        $db->execute($sql);

        return true;
    }

    /**
     * 删除表
     *
     * @return bool
     */
    public function drop()
    {
        $sql = 'DROP TABLE ' . $this->quote . $this->tableName . $this->quote;
        $this->lastSql = array($sql, []);

        $db = Be::getDb($this->db);
        $db->execute($sql);

        return true;
    }

    /**
     * 初始化
     *
     * @return $this
     */
    public function init()
    {
        $this->join = array();
        $this->where = array();
        $this->groupBy = '';
        $this->having = '';
        $this->offset = 0;
        $this->limit = 0;
        $this->orderBy = '';

        return $this;
    }

    /**
     * 准备查询的 sql
     *
     * @return array
     * @throws Exception
     */
    public function prepareSql()
    {
        $sql = '';
        $values = array();

        // 处理 where 条件
        if (count($this->where) > 0) {
            $sql .= ' WHERE ';
            foreach ($this->where as $where) {
                if (is_array($where)) {
                    if (is_array($where[1])) {
                        $sql .= ' ' . $where[0];
                        $values = array_merge($values, $where[1]);
                    } else {
                        $sql .= $this->quote . $where[0] . $this->quote . ' ';
                        $op = strtoupper($where[1]);
                        $sql .= $op;

                        switch ($op) {
                            case 'IN':
                            case 'NOT IN':
                                if (is_array($where[2]) && count($where[2])>0) {
                                    $sql .= ' (' . implode(',', array_fill(0, count($where[2]), '?')) . ')';
                                    $values = array_merge($values, $where[2]);
                                } else {
                                    throw new Exception('IN 查询条件异常！');
                                }
                                break;
                            case 'BETWEEN':
                            case 'NOT BETWEEN':
                                if (is_array($where[2]) && count($where[2]) == 2) {
                                    $sql .= ' ? AND ?';
                                    $values = array_merge($values, $where[2]);
                                } else {
                                    throw new Exception('BETWEEN 查询条件异常！');
                                }
                                break;
                            default:
                                $sql .= ' ?';
                                $values[] = $where[2];
                        }
                    }
                } else {
                    $sql .= ' ' . $where;
                }
            }
        }

        if ($this->groupBy) $sql .= ' GROUP BY ' . $this->groupBy;
        if ($this->having) $sql .= ' HAVING ' . $this->having;
        if ($this->orderBy) $sql .= ' ORDER BY ' . $this->orderBy;

        if ($this->limit > 0) {
            if ($this->offset > 0) {
                $sql .= ' LIMIT ' . $this->offset . ',' . $this->limit;
            } else {
                $sql .= ' LIMIT ' . $this->limit;
            }
        } else {
            if ($this->offset > 0) {
                $sql .= ' OFFSET ' . $this->offset;
            }
        }

        return array($sql, $values);
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * 获取主键名
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * 获取字段列表
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * 获取最后一次执行的完整 SQL
     *
     * @return string
     */
    public function getLastSql()
    {
        if ($this->lastSql == null) return '';
        $lastSql = $this->lastSql[0];
        $values = $this->lastSql[1];
        $n = count($values);
        $i = 0;
        while (($pos = strpos($lastSql, '?')) !== false && $i < $n) {
            $lastSql = substr($lastSql, 0, $pos) . '\'' . addslashes($values[$i]) . '\'' . substr($lastSql, $pos + 1);
            $i++;
        }
        return $lastSql;
    }

}