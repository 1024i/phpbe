<?php
namespace app\system\service;

use system\be;

class link extends \system\service
{

    public function get_system_links($conditions = array())
    {
        $table_system_link = be::get_table('system_link');

        $where = $this->create_system_link_where($conditions);
        $table_system_link->where($where);

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_system_link->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'ordering';
            $order_by_dir = 'ASC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_system_link->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_system_link->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_system_link->limit($conditions['limit']);

        return $table_system_link->get_objects();
    }

    public function get_system_link_count($conditions = array())
    {
        return be::get_table('system_link')
            ->where($this->create_system_link_where($conditions))
            ->count();
    }

    private function create_system_link_where($conditions = array())
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

    public function unblock($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system_link');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
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

    public function block($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system_link');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
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

    public function delete($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system_link');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->delete()
            ) {
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

    public function update()
    {
        $links = be::get_table('system_link')
            ->where('block', 0)
            ->order_by('ordering', 'desc')
            ->get_objects();

        $config_system_link = be::get_config('system_link');
        $properties = get_object_vars($config_system_link);
        foreach ($properties as $key => $val) {
            unset($config_system_link->$key);
        }

        $i = 1;
        foreach ($links as $link) {
            $key = 'link_' . $i;
            $config_system_link->$key = array('name' => $link->name, 'url' => $link->url);
            $i++;
        }

        $service_system = be::get_service('system');
        $service_system->update_config($config_system_link, PATH_DATA . DS . 'config' . DS . 'system_link.php');
    }

}
