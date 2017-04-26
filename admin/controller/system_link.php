<?php
namespace admin\controller;

use \system\be;
use \system\request;

// 友情链接
class system_link extends \admin\system\controller
{
	
	public function links()
	{
	    $order_by = request::post('order_by', 'rank');
		$order_by_dir = request::post('order_by_dir', 'ASC');

		$key = request::post('key', '');
		$status = request::post('status', -1, 'int');
		$limit = request::post('limit', -1, 'int');

		if ($limit == -1) {
			$admin_config_system = be::get_admin_config('system');
			$limit = $admin_config_system->limit;
		}
		
		$admin_model_system_link = be::get_admin_model('system_link');
		$template = be::get_admin_template('system_link.links');
        $template->set_title('友情链接');

		$option = array('key'=>$key, 'status'=>$status);

		$pagination = be::get_admin_ui('pagination');
		$pagination->set_limit($limit);
		$pagination->set_total($admin_model_system_link->get_system_link_count($option));
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
		
		$system_links = $admin_model_system_link->get_system_links($option);
		$template->set('system_links', $system_links);

		$template->display();

		$lib_history = be::get_lib('history');
		$lib_history->save();
	}
	
	
    public function edit()
	{
		$id = request::post('id',0 ,'int');
        
		$row_system_link = be::get_row('system_link');
		if ($id>0) $row_system_link->load($id);
        
		$template = be::get_admin_template('system_link.edit');
		if ($id == 0)
			$template->set_title('添加友情链接');
		else
			$template->set_title('编辑友情链接');

		$template->set('system_link', $row_system_link);

		$template->display();
	}
	


	public function edit_save()
	{
		$id = request::post('id',0 ,'int');

		$row_system_link = be::get_row('system_link');
		if ($id>0) $row_system_link->load($id);
		
		$row_system_link->bind(request::post());

		if ($row_system_link->save()) {
            $admin_model_system_link = be::get_admin_model('system_link');
            $admin_model_system_link->update();
            
			if ($id == 0) {
				$this->set_message('添加友情链接成功！');
				system_log('添加友情链接：'.$row_system_link->name);
			} else {
				$this->set_message('修改友情链接成功！');
				system_log('修改友情链接：'.$row_system_link->name);
			}
		} else {
			$this->set_message($row_system_link->get_error(), 'error');
		}

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
	
	public function unblock()
	{
        $ids = request::post('id', '');

        $admin_model_system_link = be::get_admin_model('system_link');
        if ($admin_model_system_link->unblock($ids)) {
            $admin_model_system_link->update();
            
            $this->set_message('公开友情链接成功！');
            system_log('公开友情链接：#'.$ids);
        }
        else
            $this->set_message($admin_model_system_link->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
	public function block()
	{
        $ids = request::post('id', '');

        $admin_model_system_link = be::get_admin_model('system_link');
        if ($admin_model_system_link->block($ids)) {
            $admin_model_system_link->update();
            
            $this->set_message('屏蔽友情链接成功！');
            system_log('屏蔽友情链接：#'.$ids);
        }
        else
            $this->set_message($admin_model_system_link->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
	public function delete()
	{
        $ids = request::post('id', '');

        $admin_model_system_link = be::get_admin_model('system_link');
        if ($admin_model_system_link->delete($ids)) {
            $admin_model_system_link->update();
            
            $this->set_message('删除友情链接成功！');
            system_log('删除友情链接：#'.$ids);
        }
        else
            $this->set_message($admin_model_system_link->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	


}
?>