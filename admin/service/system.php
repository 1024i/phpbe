<?php

namespace admin\service;

use system\be;

class system extends \system\service
{

    public function watermark($image)
    {
        $lib_image = be::get_lib('image');
        $lib_image->open($image);

        if (!$lib_image->is_image()) {
            $this->set_error('不是合法的图片！');
            return false;
        }

        $width = $lib_image->get_width();
        $height = $lib_image->get_height();

        $config_watermark = be::get_config('watermark');

        $x = 0;
        $y = 0;
        switch ($config_watermark->position) {
            case 'north':
                $x = $width / 2 + $config_watermark->offset_x;
                $y = $config_watermark->offset_y;
                break;
            case 'northeast':
                $x = $width + $config_watermark->offset_x;
                $y = $config_watermark->offset_y;
                break;
            case 'east':
                $x = $width + $config_watermark->offset_x;
                $y = $height / 2 + $config_watermark->offset_y;
                break;
            case 'southeast':
                $x = $width + $config_watermark->offset_x;
                $y = $height + $config_watermark->offset_y;
                break;
            case 'south':
                $x = $width / 2 + $config_watermark->offset_x;
                $y = $height + $config_watermark->offset_y;
                break;
            case 'southwest':
                $x = $config_watermark->offset_x;
                $y = $height + $config_watermark->offset_y;
                break;
            case 'west':
                $x = $config_watermark->offset_x;
                $y = $height / 2 + $config_watermark->offset_y;
                break;
            case 'northwest':
                $x = $config_watermark->offset_x;
                $y = $config_watermark->offset_y;
                break;
            case 'center':
                $x = $width / 2 + $config_watermark->offset_x;
                $y = $height / 2 + $config_watermark->offset_y;
                break;
        }

        $x = intval($x);
        $y = intval($y);

        if ($config_watermark->type == 'text') {
            $style = array();
            $style['font_size'] = $config_watermark->text_size;
            $style['color'] = $config_watermark->text_color;

            // 添加文字水印
            $lib_image->text($config_watermark->text, $x, $y, 0, $style);
        } else {
            // 添加图像水印
            $lib_image->watermark(PATH_DATA . DS . 'system' . DS . 'watermark' . DS . $config_watermark->image, $x, $y);
        }

        $lib_image->save($image);

        return true;
    }

    public function new_log($log)
    {
        $my = be::get_admin_user();
        $row_system_log = be::get_row('system_log');
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
        $table_system_log = be::get_table('system_log');

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
        return be::get_table('system_log')
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
        return be::get_table('system_log')->where('create_time', '<', (time() - 90 * 86400))->delete();
    }

    /**
     * 获取管理员列表
     *
     * @return array
     */
    public function get_admin_users()
    {
        return be::get_table('admin_user')->get_objects();
    }

}
