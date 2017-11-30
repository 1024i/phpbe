<?php

namespace admin\controller;

use system\be;
use system\request;
use system\response;

// 公告
class system_announcement extends \admin\system\controller
{

    public function announcements()
    {
        $order_by = request::post('order_by', 'id');
        $order_by_dir = request::post('order_by_dir', 'DESC');

        $key = request::post('key', '');
        $status = request::post('status', -1, 'int');
        $limit = request::post('limit', -1, 'int');

        if ($limit == -1) {
            $admin_config_system = be::get_admin_config('system');
            $limit = $admin_config_system->limit;
        }

        $admin_service_system_announcement = be::get_admin_service('system_announcement');
        $template = be::get_admin_template('system_announcement.announcements');
        response::set_title('公告');

        $option = array('key' => $key, 'status' => $status);

        $pagination = be::get_admin_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_system_announcement->get_system_announcement_count($option));
        $pagination->set_page(request::post('page', 1, 'int'));

        response::set('pagination', $pagination);
        response::set('order_by', $order_by);
        response::set('order_by_dir', $order_by_dir);
        response::set('key', $key);
        response::set('status', $status);

        $option['order_by'] = $order_by;
        $option['order_by_dir'] = $order_by_dir;
        $option['offset'] = $pagination->get_offset();
        $option['limit'] = $limit;

        $system_announcements = $admin_service_system_announcement->get_system_announcements($option);
        response::set('system_announcements', $system_announcements);

        response::display();

        $lib_history = be::get_lib('history');
        $lib_history->save();
    }


    public function edit()
    {
        $id = request::post('id', 0, 'int');

        $row_system_announcement = be::get_row('system_announcement');
        if ($id > 0) $row_system_announcement->load($id);

        $template = be::get_admin_template('system_announcement.edit');
        if ($id == 0)
            response::set_title('添加公告');
        else
            response::set_title('编辑公告');

        response::set('system_announcement', $row_system_announcement);

        response::display();
    }


    public function edit_save()
    {
        $id = request::post('id', 0, 'int');

        $row_system_announcement = be::get_row('system_announcement');
        if ($id > 0) $row_system_announcement->load($id);

        $row_system_announcement->bind(request::post());

        $row_system_announcement->create_time = strtotime($row_system_announcement->create_time);
        $row_system_announcement->body = request::post('body', '', 'html');

        if ($row_system_announcement->save()) {
            if ($id == 0) {
                response::set_message('添加公告成功！');
                system_log('添加公告：' . $row_system_announcement->title);
            } else {
                response::set_message('修改公告成功！');
                system_log('修改公告：' . $row_system_announcement->title);
            }
        } else {
            response::set_message($row_system_announcement->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }


    public function unblock()
    {
        $ids = request::post('id', '');

        $admin_service_system_announcement = be::get_admin_service('system_announcement');
        if ($admin_service_system_announcement->unblock($ids)) {
            response::set_message('公开公告成功！');
            system_log('公开公告：#' . $ids);
        } else
            response::set_message($admin_service_system_announcement->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function block()
    {
        $ids = request::post('id', '');

        $admin_service_system_announcement = be::get_admin_service('system_announcement');
        if ($admin_service_system_announcement->block($ids)) {
            response::set_message('屏蔽公告成功！');
            system_log('屏蔽公告：' . $ids);
        } else
            response::set_message($admin_service_system_announcement->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function delete()
    {
        $ids = request::post('id', '');

        $admin_service_system_announcement = be::get_admin_service('system_announcement');
        if ($admin_service_system_announcement->delete($ids)) {
            response::set_message('删除公告成功！');
            system_log('删除公告：' . $ids);
        } else
            response::set_message($admin_service_system_announcement->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }


}

?>