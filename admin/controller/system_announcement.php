<?php
namespace admin\controller;

// 公告
class system_announcement extends \admin\controller
{
	
	public function announcements()
	{
	    $order_by = request::post('order_by', 'id');
		$order_by_dir = request::post('order_by_dir', 'DESC');

		$key = request::post('key', '');
		$status = post::int('status', -1);
		$limit = post::int('limit', -1);

		if ($limit == -1) {
			$admin_config_system = be::get_admin_config('system');
			$limit = $admin_config_system->limit;
		}
		
		$admin_model_system_announcement = be::get_admin_model('system_announcement');
		$template = be::get_admin_template('system_announcement.announcements');
        $template->set_title('公告');

		$option = array('key'=>$key, 'status'=>$status);

		$pagination = be::get_admin_ui('pagination');
		$pagination->set_limit($limit);
		$pagination->set_total($admin_model_system_announcement->get_system_announcement_count($option));
		$pagination->set_page(request::post('page', 1, 'int'));

		$template->set('pagination', $pagination);
		$template->set('order_by', $order_by);
		$template->set('order_by_dir', $order_by_dir);
		$template->set('key', $key);
		$template->set('status', $status);

		$option['order_by'] = $order_by;
		$option['order_by_dir'] = $order_by_dir;
		$option['offset'] = $pagination->get_offset();
		$option['limit'] = $limit;
		
		$system_announcements = $admin_model_system_announcement->get_system_announcements($option);
		$template->set('system_announcements', $system_announcements);

		$template->display();

		$lib_history = be::get_lib('history');
		$lib_history->save();
	}
	
	
    public function edit()
	{
		$id = request::post('id',0 ,'int');
        
		$row_system_announcement = be::get_row('system_announcement');
		if ($id>0) $row_system_announcement->load($id);

		$template = be::get_admin_template('system_announcement.edit');
		if ($id == 0)
			$template->set_title('添加公告');
		else
			$template->set_title('编辑公告');

		$template->set('system_announcement', $row_system_announcement);

		$template->display();
	}
	


	public function edit_save()
	{
		$id = request::post('id',0 ,'int');

		$row_system_announcement = be::get_row('system_announcement');
		if ($id>0) $row_system_announcement->load($id);
		
		$row_system_announcement->bind(post::_());

		$row_system_announcement->create_time = strtotime($row_system_announcement->create_time);
		$row_system_announcement->body = post::html('body');

		if ($row_system_announcement->save()) {
			if ($id == 0) {
				$this->set_message('添加公告成功！');
				system_log('添加公告：'.$row_system_announcement->title);
			} else {
				$this->set_message('修改公告成功！');
				system_log('修改公告：'.$row_system_announcement->title);
			}
		} else {
			$this->set_message($row_system_announcement->get_error(), 'error');
		}

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
	
	public function unblock()
	{
        $ids = request::post('id', '');
        
        $admin_model_system_announcement = be::get_admin_model('system_announcement');
        if ($admin_model_system_announcement->unblock($ids)) {
            $this->set_message('公开公告成功！');
            system_log('公开公告：#'.$ids);
        }
        else
            $this->set_message($admin_model_system_announcement->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
	public function block()
	{
        $ids = request::post('id', '');

        $admin_model_system_announcement = be::get_admin_model('system_announcement');
        if ($admin_model_system_announcement->block($ids)) {
            $this->set_message('屏蔽公告成功！');
            system_log('屏蔽公告：'.$ids);
        }
        else
            $this->set_message($admin_model_system_announcement->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
	public function delete()
	{
        $ids = request::post('id', '');

        $admin_model_system_announcement = be::get_admin_model('system_announcement');
        if ($admin_model_system_announcement->delete($ids)) {
            $this->set_message('删除公告成功！');
            system_log('删除公告：'.$ids);
        }
        else
            $this->set_message($admin_model_system_announcement->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	


}
?>