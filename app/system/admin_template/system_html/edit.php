<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_ui('editor');
$ui_editor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$system_html = $this->get('system_html');

$ui_editor = be::get_ui('editor');

$ui_editor->set_action('save', './?controller=system_html&task=edit_save');	// 显示提交按钮
$ui_editor->set_action('reset');	// 显示重设按钮
$ui_editor->set_action('back');	// 显示返回按钮

$ui_editor->set_fields(
    array(
        'type'=>'text',
        'name'=>'name',
        'label'=>'名称',
        'value'=>$system_html->name,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'class',
        'label'=>'调用名',
        'value'=>$system_html->class,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true,
            'remote'=>'./?controller=system_html&task=check_class&id='.$system_html->id
       ),
        'message'=>array(
            'remote'=>'调用名已被占用！'
       )
   ),
    array(
        'type'=>'richtext',
        'name'=>'body',
        'label'=>'内容',
        'value'=>$system_html->body,
        'width'=>'600px',
        'height'=>'360px'
   ),
    array(
        'type'=>'radio',
        'name'=>'block',
        'label'=>'状态',
        'value'=>$system_html->block,
        'options'=>array('0'=>'公开','1'=>'屏蔽')
    )
);

$ui_editor->add_hidden('id', $system_html->id);
$ui_editor->display();
?>
<!--{/center}-->