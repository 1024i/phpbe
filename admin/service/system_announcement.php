<?php

namespace admin\service;

use system\be;

class system_announcement extends \system\service
{

    public function get_system_announcements($conditions = [])
    {
        $table_system_announcement = be::get_table('system_announcement');

        $where = $this->create_system_announcement_where($conditions);
        $table_system_announcement->where($where);

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_system_announcement->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'rank';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_system_announcement->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_system_announcement->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_system_announcement->limit($conditions['limit']);

        return $table_system_announcement->get_objects();
    }


    public function get_system_announcement_count($conditions = [])
    {
        return be::get_table('system_announcement')
            ->where($this->create_system_announcement_where($conditions))
            ->count();
    }

    private function create_system_announcement_where($conditions = [])
    {
        $where = [];

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = '(';
            $where[] = ['title', 'like', '%' . $conditions['key'] . '%'];
            $where[] = 'OR';
            $where[] = ['content', 'like', '%' . $conditions['key'] . '%'];
            $where[] = ')';
        }

        if (isset($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        return $where;
    }


    public function unblock($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('system_announcement');
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

            $table = be::get_table('system_announcement');
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

            $table = be::get_table('system_announcement');
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
}