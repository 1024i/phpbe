<?php
namespace app\system\service;

use system\be;

class html extends \system\service
{

    /**
     * 获取自定义模块列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_system_htmls($conditions = array())
    {
        $table_system_html = be::get_table('system_html');

        $where = $this->create_system_html_where($conditions);
        $table_system_html->where($where);

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_system_html->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'id';
            $order_by_dir = 'ASC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_system_html->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_system_html->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_system_html->limit($conditions['limit']);

        return $table_system_html->get_objects();
    }

    /**
     * 获取自定义模块总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_system_html_count($conditions = array())
    {
        return be::get_table('system_html')
            ->where($this->create_system_html_where($conditions))
            ->count();
    }

    /**
     * 跟据查询条件生成 where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function create_system_html_where($conditions = array())
    {
        $where = array();

        if (array_key_exists('key', $conditions) && $conditions['key']) {
            $where[] = array('title', 'like', '%' . $conditions['key'] . '%');
        }

        if (array_key_exists('status', $conditions) && $conditions['status'] != -1) {
            $where[] = array('block', $conditions['status']);
        }

        return $where;
    }

    /**
     * 类名是否可用
     *
     * @param string $class 模块的类名
     * @param int $id
     * @return bool
     */
    public function is_class_available($class, $id)
    {
        $table = be::get_table('system_html');
        if ($id > 0) {
            $table->where('id', '!=', $id);
        }
        $table->where('class', $class);
        return $table->count() == 0;
    }

    /**
     * 公开
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @return bool
     */
    public function unblock($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $ids = explode(',', $ids);

            $table = be::get_table('system_html');
            if (!$table->where('id', 'in', $ids)->update(['block' => 0])) {
                throw new \exception($table->get_error());
            }

            $objects = $table->where('id', 'in', $ids)->get_objects();

            $dir = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html';
            if (!file_exists($dir)) {
                $lib_fso = be::get_lib('fso');
                $lib_fso->mk_dir($dir);
            }

            foreach ($objects as $obj) {
                file_put_contents($dir . DS . $obj->class . '.html', $obj->body);
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 屏蔽
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @return bool
     */
    public function block($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $ids = explode(',', $ids);

            $table = be::get_table('system_html');
            if (!$table->where('id', 'in', $ids)->update(['block' => 1])) {
                throw new \exception($table->get_error());
            }

            $classes = $table->where('id', 'in', $ids)->get_values('class');

            $dir = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html';
            foreach ($classes as $class) {
                $path = $dir . DS . $class . '.html';
                if (file_exists($path)) @unlink($path);
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 删除
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @return bool
     */
    public function delete($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $ids = explode(',', $ids);

            $table = be::get_table('system_html');
            $classes = $table->where('id', 'in', $ids)->get_values('class');

            $dir = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html';
            foreach ($classes as $class) {
                $path = $dir . DS . $class . '.html';
                if (file_exists($path)) @unlink($path);
            }

            if (!$table->where('id', 'in', $ids)->delete()) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }


}
