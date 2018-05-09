<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();
?>
<script type="text/javascript" language="javascript" src="template/system/js/configMail.js"></script>
<!--{/head}-->

<!--{center}-->
<?php

$config = $this->get('config');

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?app=System&controller=System&task=configMailSave');
$uiEditor->setAction('back', './?app=System&controller=System&task=configMailTest', '发送邮件测试');

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'fromMail',
        'label'=>'发信邮件',
        'value'=>$config->fromMail,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true,
            'email'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'fromName',
        'label'=>'发信人',
        'value'=>$config->fromName,
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
        'name'=>'smtpHost',
        'label'=>'SMTP服务器',
        'value'=>$config->smtpHost,
        'width'=>'300px'
   ),
    array(
        'type'=>'text',
        'name'=>'smtpPort',
        'label'=>'SMTP端口',
        'value'=>$config->smtpPort,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true,
            'max'=>65535
       )
   ),
    array(
        'type'=>'text',
        'name'=>'smtpUser',
        'label'=>'SMTP用户名',
        'value'=>$config->smtpUser,
        'width'=>'240px'
   ),
    array(
        'type'=>'text',
        'name'=>'smtpPass',
        'label'=>'SMTP密码',
        'value'=>$config->smtpPass,
        'width'=>'240px'
   ),
    array(
        'type'=>'radio',
        'name'=>'smtpSecure',
        'label'=>'加密',
        'value'=>$config->smtpSecure,
        'options'=>array('0'=>'无', 'ssl'=>'SSL', 'tls'=>'TLS')
   )
);
        
$uiEditor->display();
?>
<!--{/center}-->