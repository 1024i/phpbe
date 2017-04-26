<?php
namespace admin\controller;

use \system\be;
use \system\request;

// 自定义模块
class system_html extends \admin\system\controller
{
	
	public function htmls()
	{
	    $order_by = request::post('order_by', 'id');
		$order_by_dir = request::post('order_by_dir', 'ASC');

		$key = request::post('key', '');
		$status = request::post('status', -1, 'int');
		$limit = request::post('limit', -1, 'int');

		if ($limit == -1) {
			$admin_config_system = be::get_admin_config('system');
			$limit = $admin_config_system->limit;
		}
		
		$admin_model_system_html = be::get_admin_model('system_html');
		$template = be::get_admin_template('system_html.htmls');
        $template->set_title('自定义模块');

		$option = array('key'=>$key, 'status'=>$status);

		$pagination = be::get_admin_ui('pagination');
		$pagination->set_limit($limit);
		$pagination->set_total($admin_model_system_html->get_system_html_count($option));
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
		
		$system_htmls = $admin_model_system_html->get_system_htmls($option);
		$template->set('system_htmls', $system_htmls);

		$template->display();

		$lib_history = be::get_lib('history');
		$lib_history->save();
	}
	
	
    public function edit()
	{
		$id = request::post('id',0 ,'int');
        
		$row_system_html = be::get_row('system_html');
		if ($id>0) $row_system_html->load($id);

		$template = be::get_admin_template('system_html.edit');
		if ($id == 0)
			$template->set_title('添加自定义模块');
		else
			$template->set_title('编辑自定义模块');

		$template->set('system_html', $row_system_html);

		$template->display();
	}
	


	public function edit_save()
	{
		$id = request::post('id',0 ,'int');

		$row_system_html = be::get_row('system_html');
		if ($id>0) $row_system_html->load($id);
		
		$row_system_html->bind(post::_());
		$row_system_html->body = post::html('body');

		if ($row_system_html->save()) {
			$clean_body = post::html('body', '');
            $dir = PATH_DATA.DS.'system'.DS.'html';
            if (!file_exists($dir)) {
                $lib_fso = be::get_lib('fso');
                $lib_fso->mk_dir($dir);
            }
			file_put_contents($dir.DS.$row_system_html->class.'.html', $clean_body);

			if ($id == 0) {
				$this->set_message('添加自定义模块成功！');
				system_log('添加自定义模块：'.$row_system_html->name);
			} else {
				$this->set_message('修改自定义模块成功！');
				system_log('修改自定义模块：#'.$id.': '.$row_system_html->name);
			}
		} else {
			$this->set_message($row_system_html->get_error(), 'error');
		}

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
    public function check_class()
    {
        $id = request::get('id', 0, 'int');
        $class = request::get('class','');
        
        $admin_model_system_html = be::get_admin_model('system_html');
        echo $admin_model_system_html->is_class_available($class, $id) ? 'true' : 'false';
    }

	public function unblock()
	{
        $ids = request::post('id', '');

        $admin_model_system_html = be::get_admin_model('system_html');
        
        if ($admin_model_system_html->unblock($ids)) {
            $this->set_message('公开自定义模块成功！');
            system_log('公开自定义模块：#'.$ids);
        }
        else
            $this->set_message($admin_model_system_html->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
	
	public function block()
	{
        $ids = request::post('id', '');

        $admin_model_system_html = be::get_admin_model('system_html');
        if ($admin_model_system_html->block($ids)) {
            $this->set_message('屏蔽自定义模块成功！');
            system_log('屏蔽自定义模块：#'.$ids);
        }
        else
            $this->set_message($admin_model_system_html->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
    
	public function delete()
	{
        $ids = request::post('id', '');

        $admin_model_system_html = be::get_admin_model('system_html');
        if ($admin_model_system_html->delete($ids)) {
            $this->set_message('删除自定义模块成功！');
            system_log('删除自定义模块：#'.$ids);
        }
        else
            $this->set_message($admin_model_system_html->get_error(), 'error');

		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
}
?>