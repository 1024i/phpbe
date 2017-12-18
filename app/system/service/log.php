<?php

namespace app\system\service;

use system\be;

class log extends \system\service
{

    public function new_log($log)
    {
        $my = be::get_admin_user();
        $row_system_log = be::get_row('system.log');
        $row_system_log->user_id = $my->id;
        $row_system_log->title = $log;
        $row_system_log->ip = $_SERVER['REMOTE_ADDR'];
        $row_system_log->create_time = time();
        $row_system_log->save();
    }

    /**
     * 获取系统操作日志列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_logs($conditions = array())
    {
        $table_system_log = be::get_table('system.log');

        $where = $this->create_log_where($conditions);
        $table_system_log->where($where);

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_system_log->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'id';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_system_log->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_system_log->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_system_log->limit($conditions['limit']);

        return $table_system_log->get_objects();
    }

    /**
     * 获取系统操作日志总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_log_count($conditions = array())
    {
        return be::get_table('system.log')
            ->where($this->create_log_where($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where 数组
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function create_log_where($conditions = [])
    {
        $where = [];

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '%' . $conditions['key'] . '%'];
        }

        if (isset($conditions['user_id']) && is_numeric($conditions['user_id']) && $conditions['user_id'] != 0) {
            $where[] = ['user_id', $conditions['user_id']];
        }

        return $where;
    }


    /**
     * 删除三个月(90天)前的后台用户登陆日志
     *
     * @return bool
     */
    public function delete_logs()
    {
        return be::get_table('system.log')->where('create_time', '<', (time() - 90 * 86400))->delete();
    }


}
