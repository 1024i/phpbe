<?php
namespace app\system\admin_controller;

use system\be;
use system\request;
use system\response;

// 友情链接
class link extends \system\admin_controller
{

    public function links()
    {
        $order_by = request::post('order_by', 'rank');
        $order_by_dir = request::post('order_by_dir', 'ASC');

        $key = request::post('key', '');
        $status = request::post('status', -1, 'int');
        $limit = request::post('limit', -1, 'int');

        if ($limit == -1) {
            $admin_config_system = be::get_config('system.admin');
            $limit = $admin_config_system->limit;
        }

        $admin_service_system_link = be::get_service('system.link');
        response::set_title('友情链接');

        $option = array('key' => $key, 'status' => $status);

        $pagination = be::get_ui('pagination');
        $pagination->set_limit($limit);
        $pagination->set_total($admin_service_system_link->get_system_link_count($option));
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

        $system_links = $admin_service_system_link->get_system_links($option);
        response::set('system_links', $system_links);

        response::display();

        $lib_history = be::get_lib('history');
        $lib_history->save();
    }


    public function edit()
    {
        $id = request::post('id', 0, 'int');

        $row_system_link = be::get_row('system.link');
        if ($id > 0) $row_system_link->load($id);

        if ($id == 0)
            response::set_title('添加友情链接');
        else
            response::set_title('编辑友情链接');

        response::set('system_link', $row_system_link);

        response::display();
    }


    public function edit_save()
    {
        $id = request::post('id', 0, 'int');

        $row_system_link = be::get_row('system.link');
        if ($id > 0) $row_system_link->load($id);

        $row_system_link->bind(request::post());

        if ($row_system_link->save()) {
            $admin_service_system_link = be::get_service('system.link');
            $admin_service_system_link->update();

            if ($id == 0) {
                response::set_message('添加友情链接成功！');
                system_log('添加友情链接：' . $row_system_link->name);
            } else {
                response::set_message('修改友情链接成功！');
                system_log('修改友情链接：' . $row_system_link->name);
            }
        } else {
            response::set_message($row_system_link->get_error(), 'error');
        }

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }


    public function unblock()
    {
        $ids = request::post('id', '');

        $admin_service_system_link = be::get_service('system.link');
        if ($admin_service_system_link->unblock($ids)) {
            $admin_service_system_link->update();

            response::set_message('公开友情链接成功！');
            system_log('公开友情链接：#' . $ids);
        } else
            response::set_message($admin_service_system_link->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function block()
    {
        $ids = request::post('id', '');

        $admin_service_system_link = be::get_service('system.link');
        if ($admin_service_system_link->block($ids)) {
            $admin_service_system_link->update();

            response::set_message('屏蔽友情链接成功！');
            system_log('屏蔽友情链接：#' . $ids);
        } else
            response::set_message($admin_service_system_link->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }

    public function delete()
    {
        $ids = request::post('id', '');

        $admin_service_system_link = be::get_service('system.link');
        if ($admin_service_system_link->delete($ids)) {
            $admin_service_system_link->update();

            response::set_message('删除友情链接成功！');
            system_log('删除友情链接：#' . $ids);
        } else
            response::set_message($admin_service_system_link->get_error(), 'error');

        $lib_history = be::get_lib('history');
        $lib_history->back();
    }


}

?>