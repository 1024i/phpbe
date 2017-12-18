<?php
namespace system;

use system\db\exception;

/**
 * 数据库表行记录
 */
abstract class row extends obj
{
    protected $db = 'master';
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
     * 切换库
     *
     * @param string $db db配置文件中的库名
     * @return row
     */
    public function db($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * 绑定一个数据源， GET, POST, 或者一个数组, 对象
     *
     * @param string | array | object $data 要绑定的数据对象
     * @return \system\row | bool
     * @throws exception
     */
    public function bind($data)
    {
        if (!is_object($data) && !is_array($data)) {
            throw new exception('绑定失败，不合法的数据源！');
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
     * @throws exception
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
                throw new exception('row->load() 方法参数错误！');
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
            $db = be::get_db($this->db);
            $row = $db->get_object($sql, $values);
        }

        if (!$row) {
            throw new exception('未找到指定数据记录！');
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
        $db = be::get_db($this->db);

        $primary_key = $this->primary_key;
        if ($this->$primary_key) {
            $db->update($this->table_name, $this, $this->primary_key);
        } else {
            $db->insert($this->table_name, $this);
            $this->$primary_key = $db->get_last_insert_id();
        }

        return true;
    }

    /**
     * 删除指定主键值的记录
     *
     * @param int $id 主键值
     * @return bool
     * @throws exception
     */
    public function delete($id = null)
    {
        $primary_key = $this->primary_key;
        if ($id === null) $id = $this->$primary_key;

        if ($id === null) {
            throw new exception('参数缺失, 请指定要删除记录的编号！');
        }

        $db = be::get_db($this->db);
        $db->execute('DELETE FROM ' . $this->quote . $this->table_name . $this->quote . ' WHERE ' . $this->quote . $this->primary_key . $this->quote . '=?', array($id));

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

        $db = be::get_db($this->db);
        $db->execute($sql, array($id));

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

        $db = be::get_db($this->db);
        $db->execute($sql, array($id));

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
