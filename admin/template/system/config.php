<?php
namespace admin\template\system;

class config extends \admin\theme
{

	protected function head()
	{
		$ui_editor = be::get_admin_ui('editor');
		$ui_editor->set_left_width(200);
		$ui_editor->head();
	}
	
	protected function center()
	{
	    $config = $this->get('config');
	    
        $ui_editor = be::get_admin_ui('editor');
		
		$ui_editor->set_action('save', './?controller=system&task=config_save');

		$ui_editor->set_fields(
            array(
            	'type'=>'radio', 
            	'name'=>'offline', 
            	'label'=>'启用/关闭网站',
            	'value'=>$config->offline,
                'options'=>array('0'=>'启用', '1'=>'关闭')
           ),
            array(
            	'type'=>'richtext', 
            	'name'=>'offline_message', 
            	'label'=>'关闭网站时提示信息', 
            	'value'=>$config->offline_message, 
            	'width'=>'500px', 
            	'height'=>'45px'
           ),
            array(
            	'type'=>'text', 
            	'name'=>'site_name', 
            	'label'=>'关闭网站时提示信息',
				'width'=>'400px', 
            	'value'=>$config->site_name
           ),
            array(
            	'type'=>'radio', 
            	'name'=>'sef', 
            	'label'=>'伪静态页', 
            	'value'=>$config->sef,
                'options'=>array('1'=>'启用', '0'=>'关闭')
           ),
			array(
            	'type'=>'text', 
            	'name'=>'sef_suffix', 
            	'label'=>'伪静态页后缀', 
				'width'=>'90px', 
            	'value'=>$config->sef_suffix
           ),
			array(
            	'type'=>'text', 
            	'name'=>'home_title', 
            	'label'=>'首页标题', 
				'width'=>'400px', 
            	'value'=>$config->home_title
           ),
			array(
            	'type'=>'text', 
            	'name'=>'home_meta_keywords', 
            	'label'=>'首页 META 关键词', 
				'width'=>'500px', 
            	'value'=>$config->home_meta_keywords
           ),
			array(
            	'type'=>'text', 
            	'name'=>'home_meta_description', 
            	'label'=>'首页 META 描述', 
				'width'=>'500px', 
            	'value'=>$config->home_meta_description
           ),
			array(
            	'type'=>'text', 
            	'name'=>'allow_upload_file_types', 
            	'label'=>'允许上传的文件类型',
				'width'=>'400px', 
            	'value'=>implode(', ', $config->allow_upload_file_types)
           ),
			array(
            	'type'=>'text', 
            	'name'=>'allow_upload_image_types', 
            	'label'=>'允许上传的图像类型',
				'width'=>'400px', 
            	'value'=>implode(', ', $config->allow_upload_image_types)
           )
       );
		$ui_editor->display();
	}	

}
?>