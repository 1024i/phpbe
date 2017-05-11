<?php

namespace system;

/**
 * 数据库表 查询器
 */
class table extends obj
{

    protected $table_name = ''; // 表名
    protected $primary_key = 'id'; // 主键
    protected $fields = []; // 字段列表

    protected $quote = '`'; // 字段或表名转义符 mysql: `

    protected $alias = ''; // 当前表的别名
    protected $join = array(); // 表连接
    protected $where = array(); // where 条件
    protected $group_by = ''; // 分组
    protected $having = ''; // having
    protected $offset = 0; // 分页编移
    protected $limit = 0; // 分页大小
    protected $order_by = ''; // 排序

    protected $last_sql = null; // 上次执行的 SQL

    /**
     * 缓存失效时间（单位：秒），0 为不使用缓存
     */
    protected $cache_expire = 0;

    /**
     * 切换表名
     *
     * @param string $table_name 表名
     * @return table
     */
    public function table($table_name)
    {
        $this->table_name = $table_name;
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
    public function left_join($table, $on)
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
    public function right_join($table, $on)
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
    public function inner_join($table, $on)
    {
        $this->join[] = array('INNER JOIN', $table, $on);
        return $this;
    }

    /**
     * 内连接 同 inner_join
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
    public function full_join($table, $on)
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
    public function cross_join($table, $on)
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
     * $table->where('user_id','in', array(1, 2, 3, 4));
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
    public function group_by($field)
    {
        $this->group_by = $field;
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
    public function order_by($field, $dir = null)
    {
        $field = trim($field);
        if ($dir == null) {
            $this->order_by = $field;
        } else {
            $dir = strtoupper(trim($dir));
            if ($dir != 'ASC' && $dir != 'DESC') {
                $this->order_by = $field;
            } else {
                $this->order_by = $this->quote . $field . $this->quote . ' ' . $dir;
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
        $this->cache_expire = intval($expire);
        return $this;
    }

    /**
     * 查询单个字段第一条记录
     *
     * @param string $field 查询的字段
     * @return string|int
     */
    public function get_result($field)
    {
        return $this->query('get_result', $field);
    }

    /**
     * 查询单个字段的所有记录
     *
     * @param string $field 查询的字段
     * @return array() 数组
     */
    public function get_results($field)
    {
        return $this->query('get_results', $field);
    }

    /**
     * 查询单条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return array() 数组
     */
    public function get_array($fields = null)
    {
        return $this->query('get_array', $fields);
    }

    /**
     * 查询多条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return array(array()) 二维数组
     */
    public function get_arrays($fields = null)
    {
        return $this->query('get_arrays', $fields);
    }

    /**
     * 查询单条记录
     *
     * @param string $fields 查询用到的字段列表
     * @return object 对象
     */
    public function get_object($fields = null)
    {
        return $this->query('get_object', $fields);
    }

    /**
     * 查询多条记发
     *
     * @param string $fields 查询用到的字段列表
     * @return array(object) 对象列表
     */
    public function get_objects($fields = null)
    {
        return $this->query('get_objects', $fields);
    }

    /**
     * 查询包含两个字段的键值对
     *
     * @param string $key_field 作为key的字段
     * @param string $val_field 作为value的字段
     * @return array() 数组
     */
    public function get_maps($key_field, $val_field)
    {
        $arrays = $this->query('get_array', $this->quote . $key_field . $this->quote . ',' . $this->quote . $val_field . $this->quote);
        $maps = array();
        if ($arrays && count($arrays) > 0) {
            foreach ($arrays as $array) {
                $maps[$array[$key_field]] = $array[$val_field];
            }
        }
        return $maps;
    }

    /**
     * 查询带索引的数组列表
     *
     * @param string $key_field 作为key的字段
     * @param string $fields 查询的字段列表
     * @return array 二维数组
     */
    public function get_array_maps($key_field, $fields = null)
    {
        $arrays = $this->query('get_arrays', $fields);
        $maps = array();
        if ($arrays && count($arrays) > 0) {
            foreach ($arrays as $array) {
                $maps[$array[$key_field]] = $array;
            }
        }
        return $maps;
    }

    /**
     * 查询带索引的对象列表
     *
     * @param string $key_field 作为key的字段
     * @param string $fields 查询的字段列表
     * @return array() 对象数组
     */
    public function get_object_maps($key_field, $fields = null)
    {
        $objects = $this->query('get_objects', $fields);
        $maps = array();
        if ($objects && count($objects) > 0) {
            foreach ($objects as $object) {
                $maps[$object->$key_field] = $object;
            }
        }
        return $maps;
    }

    /**
     * 执行数据库查询
     *
     * @param string $fn 指定数据库查询函数名
     * @param string $fields 查询用到的字段列表
     * @return mixed
     */
    private function query($fn, $fields = null)
    {
        $sql_data = $this->prepare_sql();
        $sql = null;
        if ($fields === null) {
            $sql = 'SELECT ' . $this->quote . implode($this->quote . ',' . $this->quote, $this->fields) . $this->quote;
        } else {
            $sql = 'SELECT ' . $fields;
        }
        $sql .= ' FROM ' . $this->quote . $this->table_name . $this->quote;
        if ($this->alias) {
            $sql .= ' AS ' . $this->alias;
        }
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' WHERE ' . $sql_data[0];

        $this->last_sql = array($sql, $sql_data[1]);

        $cache_key = null;
        if ($this->cache_expire > 0) {
            $cache_key = 'table:' . $fn . ':' . sha1($sql . serialize($sql_data[1]));
            $cache = cache::get($cache_key);
            if ($cache !== false) return $cache;
        }

        $result = db::$fn($sql, $sql_data[1]);
        if (false === $result) {
            $this->set_error(db::get_error());
            return false;
        }

        if ($this->cache_expire > 0) {
            cache::set($cache_key, $result, $this->cache_expire);
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
        return $this->query('get_result', 'COUNT(' . $field . ')');
    }

    /**
     * 求和
     *
     * @param string $field 字段名
     * @return number
     */
    public function sum($field)
    {
        return $this->query('get_result', 'SUM(' . $field . ')');
    }

    /**
     * 取最小值
     *
     * @param string $field 字段名
     * @return number
     */
    public function min($field)
    {
        return $this->query('get_result', 'MIN(' . $field . ')');
    }

    /**
     * 取最大值
     *
     * @param string $field 字段名
     * @return number
     */
    public function max($field)
    {
        return $this->query('get_result', 'MAX(' . $field . ')');
    }

    /**
     * 取平均值
     *
     * @param string $field 字段名
     * @return number
     */
    public function avg($field)
    {
        return $this->query('get_result', 'AVG(' . $field . ')');
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
        $sql_data = $this->prepare_sql();
        $sql = 'UPDATE ' . $this->quote . $this->table_name . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '+' . intval($step);
        $sql .= ' WHERE ' . $sql_data[0];

        db::execute($sql, $sql_data[1]);

        if (db::has_error()) {
            $this->set_error(db::get_error());
            return false;
        }

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
        $sql_data = $this->prepare_sql();
        $sql = 'UPDATE ' . $this->quote . $this->table_name . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '-' . intval($step);
        $sql .= ' WHERE ' . $sql_data[0];

        db::execute($sql, $sql_data[1]);

        if (db::has_error()) {
            $this->set_error(db::get_error());
            return false;
        }

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
        $sql_data = $this->prepare_sql();
        $sql = 'UPDATE ' . $this->quote . $this->table_name . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' SET ' . $this->quote . implode($this->quote . '=?,' . $this->quote, array_keys($values)) . $this->quote . '=?';
        $sql .= ' WHERE ' . $sql_data[0];

        if (!db::execute($sql, array_merge(array_values($values), $sql_data[1]))) {
            $this->set_error(db::get_error());
            return false;
        }

        return true;
    }

    /**
     * 删除数据
     *
     * @return bool
     */
    public function delete()
    {
        $sql_data = $this->prepare_sql();
        $sql = 'DELETE FROM ' . $this->quote . $this->table_name . $this->quote;
        foreach ($this->join as $join) {
            $sql .= $join[0] . ' ' . $this->quote . $join[1] . $this->quote . ' ON ' . $join[2];
        }
        $sql .= ' WHERE ' . $sql_data[0];

        if (!db::execute($sql, $sql_data[1])) {
            $this->set_error(db::get_error());
            return false;
        }

        return true;
    }

    /**
     * 清空表
     *
     * @return bool
     */
    public function truncate()
    {
        $sql = 'TRUNCATE TABLE ' . $this->quote . $this->table_name . $this->quote;
        if (!db::execute($sql)) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    /**
     * 删除表
     *
     * @return bool
     */
    public function drop()
    {
        $sql = 'DROP TABLE ' . $this->quote . $this->table_name . $this->quote;
        if (!db::execute($sql)) {
            $this->set_error(db::get_error());
            return false;
        }
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
        $this->group_by = '';
        $this->having = '';
        $this->offset = 0;
        $this->limit = 0;
        $this->order_by = '';

        return $this;
    }

    /**
     * 准备查询的 sql
     *
     * @return array()
     */
    public function prepare_sql()
    {

        print_r($this->where);
        $sql = '';
        $values = array();

        // 处理 where 条件
        foreach ($this->where as $where) {
            if (is_array($where)) {
                if (is_array($where[1])) {
                    $sql .= ' ' . $where[0];
                    $values = array_merge($values, $where[1]);
                } else {
                    $sql .= $this->quote . $where[0] . $this->quote . ' ' . strtoupper($where[1]);
                    if (is_array($where[2])) {
                        $sql .= ' (' . implode(',', array_fill(0, count($where[2]), '?')) . ')';
                        $values = array_merge($values, $where[2]);
                    } else {
                        $sql .= ' ?';
                        $values[] = $where[2];
                    }
                }
            } else {
                $sql .= ' ' . $where;
            }
        }

        if ($this->group_by) $sql .= ' GROUP BY ' . $this->group_by;
        if ($this->having) $sql .= ' HAVING ' . $this->having;
        if ($this->order_by) $sql .= ' ORDER BY ' . $this->order_by;

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
    public function get_table_name()
    {
        return $this->table_name;
    }

    /**
     * 获取主键名
     *
     * @return string
     */
    public function get_primary_key()
    {
        return $this->primary_key;
    }

    /**
     * 获取字段列表
     *
     * @return array
     */
    public function get_fields()
    {
        return $this->fields;
    }

    /**
     * 获取最后一次执行的完整 SQL
     *
     * @return string
     */
    public function get_last_sql()
    {
        if ($this->last_sql == null) return '';
        $last_sql = $this->last_sql[0];
        $values = $this->last_sql[1];
        $n = count($values);
        $i = 0;
        while (($pos = strpos($last_sql, '?')) !== false && $i < $n) {
            $last_sql = substr($last_sql, 0, $pos) . '\'' . addslashes($values[$i]) . '\'' . substr($last_sql, $pos + 1);
            $i++;
        }
        return $last_sql;
    }

}