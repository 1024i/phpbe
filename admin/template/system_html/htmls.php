<?php
namespace admin\template\system_html;

class htmls extends \admin\theme
{

	protected function head()
	{
		$ui_list = be::get_admin_ui('list');
		$ui_list->head();
	}

	protected function center()
	{
		$system_htmls = $this->get('system_htmls');

		$ui_list = be::get_admin_ui('list');
		
		$ui_list->set_action('list', './?controller=system_html&task=list');
		$ui_list->set_action('create', './?controller=system_html&task=edit');
		$ui_list->set_action('edit', './?controller=system_html&task=edit');
		$ui_list->set_action('unblock', './?controller=system_html&task=unblock');
		$ui_list->set_action('block', './?controller=system_html&task=block');
		$ui_list->set_action('delete', './?controller=system_html&task=delete');


		$ui_list->set_filters(
            array(
            	'type'=>'text', 
            	'name'=>'key', 
            	'label'=>'关键字', 
            	'value'=>$this->get('key'), 
            	'width'=>'120px'
           )
		);

		$ui_list->set_data($system_htmls);
		
		$ui_list->set_fields(
			array(
    			'name'=>'id',
    			'label'=>'ID',
    			'align'=>'center',
				'width'=>'30',
			    'order_by'=>'id'
			),
    		array(
    			'name'=>'name',
    			'label'=>'名称',
    			'align'=>'left'
    		),
    		array(
    			'name'=>'class',
    			'label'=>'调用名',
    			'align'=>'center',
				'width'=>'120',
			    'order_by'=>'class'
    		)
		);

		$ui_list->set_pagination($this->get('pagination'));
		$ui_list->order_by($this->get('order_by'), $this->get('order_by_dir'));
		$ui_list->display();
	}	

}
?>