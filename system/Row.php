<?php
namespace System;

use MongoDB\BSON\ObjectID;
use System\Db\Exception;

/**
 * 数据库表行记录
 */
abstract class Row extends Obj
{
    protected $db = 'master';
    protected $tableName = '';
    protected $primaryKey = '';

    protected $quote = '`'; // 字段或表名转义符 mysql: `

    /**
     * 构造函数
     *
     * @param string $rowName 表名
     * @param string $primaryKey 主键名
     */
    public function __construct($rowName, $primaryKey = 'id')
    {
        $this->tableName = $rowName;
        $this->primaryKey = $primaryKey;
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
     * @return \System\row | bool
     * @throws Exception
     */
    public function bind($data)
    {
        if (!is_object($data) && !is_array($data)) {
            throw new Exception('绑定失败，不合法的数据源！');
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
     * @return \System\row | false
     * @throws Exception
     */
    public function load($field, $value = null)
    {
        $sql = null;
        $values = [];

        if ($value === null) {
            if (is_array($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE';
                foreach ($field as $key => $val) {
                    $sql .= ' ' . $this->quote . $key . $this->quote . '=? AND';
                    $values[] = $val;
                }
                $sql = substr($sql, 0, -4);
            } elseif (is_numeric($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . ' = \'' . intval($field) . '\'';
            } elseif (is_string($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $field;
            }
        } else {
            if (is_array($field)) {
                throw new Exception('row->load() 方法参数错误！');
            }
            $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $this->quote . $field . $this->quote . '=?';
            $values[] = $value;
        }

        $db = Be::getDb($this->db);
        $row = $db->getObject($sql, $values);

        if (!$row) {
            throw new Exception('未找到指定数据记录！');
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
        $db = Be::getDb($this->db);

        $primaryKey = $this->primaryKey;
        if ($this->$primaryKey) {
            $db->update($this->tableName, $this, $this->primaryKey);
        } else {
            $db->insert($this->tableName, $this);
            $this->$primaryKey = $db->getLastInsertId();
        }

        return true;
    }

    /**
     * 删除指定主键值的记录
     *
     * @param int $id 主键值
     * @return bool
     * @throws Exception
     */
    public function delete($id = null)
    {
        $primaryKey = $this->primaryKey;
        if ($id === null) $id = $this->$primaryKey;

        if ($id === null) {
            throw new Exception('参数缺失, 请指定要删除记录的编号！');
        }

        $db = Be::getDb($this->db);
        $db->execute('DELETE FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . '=?', array($id));

        return true;
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
        $primaryKey = $this->primaryKey;
        $id = $this->$primaryKey;
        $sql = 'UPDATE ' . $this->quote . $this->tableName . $this->quote . ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '+' . $step . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . '=?';

        $db = Be::getDb($this->db);
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
        $primaryKey = $this->primaryKey;
        $id = $this->$primaryKey;
        $sql = 'UPDATE ' . $this->quote . $this->tableName . $this->quote . ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '-' . $step . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . '=?';

        $db = Be::getDb($this->db);
        $db->execute($sql, array($id));

        return true;
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
     * 转成简单数组
     *
     * @return array
     */
    public function toArray() {
        $array = get_object_vars($this);
        unset($array['db'], $array['tableName'], $array['primaryKey'], $array['quote']);

        return $array;
    }

    /**
     * 转成简单对象
     *
     * @return Object
     */
    public function toObject() {
        return (Object) $this->toArray();
    }
}
