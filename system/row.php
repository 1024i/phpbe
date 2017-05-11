<?php

namespace system;

/**
 * 数据库表行记录
 */
abstract class row extends obj
{

    protected $table_name = '';
    protected $primary_key = '';

    protected $quote = '`'; // 字段或表名转义符 mysql: `

    /**
     * 缓存失效时间（单位：秒），0 为不使用缓存
     */
    protected $cache_expire = 0;

    /**
     * 构造函数
     *
     * @param string $row_name 表名
     * @param string $primary_key 主键名
     */
    public function __construct($row_name, $primary_key = 'id')
    {
        $this->table_name = $row_name;
        $this->primary_key = $primary_key;
    }

    /**
     * 绑定一个数据源， GET, POST, 或者一个数组, 对象
     *
     * @param string | array | object $data 要绑定的数据对象
     * @return \system\row | bool
     */
    public function bind($data)
    {
        if (!is_object($data) && !is_array($data)) {
            $this->set_error('绑定失败，不合法的数据源！');
            return false;
        }

        if (is_object($data)) $data = get_object_vars($data);

        $properties = get_object_vars($this);

        foreach ($properties as $key => $value) {
            if (isset($data[$key])) {
                $val = $data[$key];
                $this->$key = $val;
            }
        }

        return $this;
    }

    /**
     * 加载记录
     *
     * @param string|int|array $field 要加载数据的键名，$val == null 时，为指定的主键值加载，
     * @param string $value 要加载的键的值
     * @return \system\row | false
     */
    public function load($field, $value = null)
    {
        $sql = null;
        $values = array();

        if ($value === null) {
            if (is_array($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->table_name . $this->quote . ' WHERE';
                foreach ($field as $key => $val) {
                    $sql .= ' ' . $this->quote . $key . $this->quote . '=? AND';
                    $values[] = $val;
                }
                $sql = substr($sql, 0, -4);
            } elseif (is_numeric($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->table_name . $this->quote . ' WHERE ' . $this->quote . $this->primary_key . $this->quote . ' = \'' . intval($field) . '\'';
            } elseif (is_string($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->table_name . $this->quote . ' WHERE ' . $field;
            }
        } else {
            if (is_array($field)) {
                $this->set_error('row->load() 方法参数错误！');
                return false;
            }
            $sql = 'SELECT * FROM ' . $this->quote . $this->table_name . $this->quote . ' WHERE ' . $this->quote . $field . $this->quote . '=?';
            $values[] = $value;
        }

        $row = null;
        $cache_key = null;
        if ($this->cache_expire > 0) {
            $cache_key = 'row:load:' . sha1($sql . serialize($values));
            $cache = cache::get($cache_key);
            if ($cache !== false) $row = $cache;
        }

        if ($row === null) {
            $row = db::get_object($sql, $values);

            if (db::has_error()) {
                $this->set_error(db::get_error());
                return false;
            }
        }

        if (empty($row)) {
            $this->set_error('未找到指定数据记录！');
            return false;
        }

        if ($this->cache_expire > 0) {
            cache::set($cache_key, $row, $this->cache_expire);
        }

        return $this->bind($row);
    }

    /**
     * 保存数据到数据库
     *
     * @return bool
     */
    public function save()
    {
        $success = null;

        $primary_key = $this->primary_key;
        if ($this->$primary_key) {
            $success = db::update($this->table_name, $this, $this->primary_key);
        } else {
            $success = db::insert($this->table_name, $this);

            $this->$primary_key = db::get_insert_id();
        }

        if (!$success) {
            $this->set_error(db::get_error());
            return false;
        }

        return true;
    }

    /**
     * 删除指定主键值的记录
     *
     * @param int $id 主键值
     * @return bool
     */
    public function delete($id = null)
    {
        $primary_key = $this->primary_key;
        if ($id === null) $id = $this->$primary_key;

        if ($id === null) {
            $this->set_error('参数缺失, 请指定要删除记录的编号！');
            return false;
        }

        db::execute('DELETE FROM ' . $this->quote . $this->table_name . $this->quote . ' WHERE ' . $this->quote . $this->primary_key . $this->quote . '=?', array($id));

        if (db::has_error()) {
            $this->set_error(db::get_error());
            return false;
        }

        return true;
    }

    /**
     * 缓存查询结果
     *
     * @param int $expire 缓存有期时间（单位：秒）
     *
     */
    public function cache($expire = 60)
    {
        $this->cache_expire = $expire;
    }

    /**
     * 自增某个字段
     *
     * @param string $field 字段名
     * @param int $step 自增量
     * @return bool
     */
    public function increment($field, $step = 1)
    {
        $primary_key = $this->primary_key;
        $id = $this->$primary_key;
        $sql = 'UPDATE ' . $this->quote . $this->table_name . $this->quote . ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '+' . $step . ' WHERE ' . $this->quote . $this->primary_key . $this->quote . '=?';
        db::execute($sql, array($id));

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
     * @param int $step 自减量
     * @return bool
     */
    public function decrement($field, $step = 1)
    {
        $primary_key = $this->primary_key;
        $id = $this->$primary_key;
        $sql = 'UPDATE ' . $this->quote . $this->table_name . $this->quote . ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '-' . $step . ' WHERE ' . $this->quote . $this->primary_key . $this->quote . '=?';
        db::execute($sql, array($id));

        if (db::has_error()) {
            $this->set_error(db::get_error());
            return false;
        }

        return true;
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

}
