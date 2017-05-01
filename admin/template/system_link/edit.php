<?php
namespace admin\template\system_link;

class edit extends \admin\theme
{

	protected function head()
	{
		$ui_editor = be::get_admin_ui('editor');
		$ui_editor->head();
	}
    
    protected function center()
    {
        $system_link = $this->get('system_link');

        $ui_editor = be::get_admin_ui('editor');

		$ui_editor->set_action('save', './?controller=system_link&task=edit_save');	// 显示提交按钮
		$ui_editor->set_action('reset');	// 显示重设按钮
		$ui_editor->set_action('back');	// 显示返回按钮
		
		$ui_editor->set_fields(
            array(
            	'type'=>'text', 
            	'name'=>'name', 
            	'label'=>'名称', 
            	'value'=>$system_link->name, 
            	'width'=>'300px', 
            	'validate'=>array(
            		'required'=>true
               )
           ),
            array(
            	'type'=>'text', 
            	'name'=>'url', 
            	'label'=>'网址', 
            	'value'=>$system_link->url,
            	'width'=>'500px',
            	'validate'=>array(
					'required'=>true,
            		'url'=>true
               )
           ),
            array(
            	'type'=>'text', 
            	'name'=>'rank', 
            	'label'=>'权重', 
            	'value'=>$system_link->rank, 
            	'width'=>'60px',
            	'validate'=>array(
            		'digits'=>true
               )
           ),
			array(
				'type'=>'radio',
			    'name'=>'block',
			    'label'=>'状态',
			    'value'=>$system_link->block,
				'options'=>array('0'=>'公开','1'=>'屏蔽')
			)
       );
		
		$ui_editor->add_hidden('id', $system_link->id);
		$ui_editor->display();
     
    }
}
?>