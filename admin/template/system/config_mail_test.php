<?php
use system\be;
use system\request;
?>

<!--{head}-->
<?php
$ui_editor = be::get_admin_ui('editor');
$ui_editor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$config = $this->get('config');

$ui_editor = be::get_admin_ui('editor');

$ui_editor->set_action('save', './?controller=system&task=config_mail_test_save', '发送');
$ui_editor->set_action('back', './?controller=system&task=config_mail', '返回');

$ui_editor->set_fields(
    array(
        'type'=>'text',
        'name'=>'to_email',
        'label'=>'收件邮箱',
        'value'=>request::get('to_email'),
        'width'=>'200px',
        'validate'=>array(
            'required'=>true,
            'email'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'subject',
        'label'=>'标题',
        'value'=>'系统邮件测试',
        'width'=>'300px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'richtext',
        'name'=>'body',
        'label'=>'内容',
        'value'=>'这是一封测试邮件。',
        'width'=>'500px',
        'height'=>'45px'
   )
);

$ui_editor->display();
?>
<!--{/center}-->