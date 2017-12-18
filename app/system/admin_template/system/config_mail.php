<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_ui('editor');
$ui_editor->head();
?>
<script type="text/javascript" language="javascript" src="template/system/js/config_mail.js"></script>
<!--{/head}-->

<!--{center}-->
<?php

$config = $this->get('config');

$ui_editor = be::get_ui('editor');

$ui_editor->set_action('save', './?controller=system&task=config_mail_save');
$ui_editor->set_action('back', './?controller=system&task=config_mail_test', '发送邮件测试');

$ui_editor->set_fields(
    array(
        'type'=>'text',
        'name'=>'from_mail',
        'label'=>'发信邮件',
        'value'=>$config->from_mail,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true,
            'email'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'from_name',
        'label'=>'发信人',
        'value'=>$config->from_name,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'radio',
        'name'=>'smtp',
        'label'=>'发送邮件服务器',
        'value'=>$config->smtp,
        'options'=>array('0'=>'服务器内置', '1'=>'SMTP')
   ),
    array(
        'type'=>'text',
        'name'=>'smtp_host',
        'label'=>'SMTP服务器',
        'value'=>$config->smtp_host,
        'width'=>'300px'
   ),
    array(
        'type'=>'text',
        'name'=>'smtp_port',
        'label'=>'SMTP端口',
        'value'=>$config->smtp_port,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true,
            'max'=>65535
       )
   ),
    array(
        'type'=>'text',
        'name'=>'smtp_user',
        'label'=>'SMTP用户名',
        'value'=>$config->smtp_user,
        'width'=>'240px'
   ),
    array(
        'type'=>'text',
        'name'=>'smtp_pass',
        'label'=>'SMTP密码',
        'value'=>$config->smtp_pass,
        'width'=>'240px'
   ),
    array(
        'type'=>'radio',
        'name'=>'smtp_secure',
        'label'=>'加密',
        'value'=>$config->smtp_secure,
        'options'=>array('0'=>'无', 'ssl'=>'SSL', 'tls'=>'TLS')
   )
);
        
$ui_editor->display();
?>
<!--{/center}-->