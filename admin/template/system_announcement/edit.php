<?php
namespace admin\template\system_announcement;

class edit extends \admin\theme
{

	protected function head()
	{
		$ui_editor = be::get_admin_ui('editor');
		$ui_editor->head();
	}
    
    protected function center()
    {
        $system_announcement = $this->get('system_announcement');

        $ui_editor = be::get_admin_ui('editor');

		$ui_editor->set_action('save', './?controller=system_announcement&task=edit_save');	// 显示提交按钮
		$ui_editor->set_action('reset');	// 显示重设按钮
		$ui_editor->set_action('back');	// 显示返回按钮
		
		$ui_editor->set_fields(
            array(
            	'type'=>'text', 
            	'name'=>'title', 
            	'label'=>'标题', 
            	'value'=>$system_announcement->title, 
            	'width'=>'75%', 
            	'validate'=>array(
            		'required'=>true
               )
           ),
            array(
            	'type'=>'richtext', 
            	'name'=>'body', 
            	'label'=>'内容', 
            	'value'=>$system_announcement->body, 
            	'width'=>'600px', 
            	'height'=>'360px'
           ),
			array(
            	'type'=>'text', 
            	'name'=>'create_time', 
            	'label'=>'发布时间', 
            	'value'=>$system_announcement->id == 0?date('Y-m-d H:i:s'):date('Y-m-d H:i:s', $system_announcement->create_time)
           ),
            array(
            	'type'=>'text', 
            	'name'=>'rank', 
            	'label'=>'权重', 
            	'value'=>$system_announcement->rank, 
            	'width'=>'60px',
            	'validate'=>array(
            		'digits'=>true
               )
           ),
			array(
				'type'=>'radio',
			    'name'=>'block',
			    'label'=>'状态',
			    'value'=>$system_announcement->block,
				'options'=>array('0'=>'公开','1'=>'屏蔽')
			)
       );
		
		$ui_editor->add_hidden('id', $system_announcement->id);
		$ui_editor->display();
     
    }
}
?>