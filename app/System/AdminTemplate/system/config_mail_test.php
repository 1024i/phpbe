<?php
use Phpbe\System\Be;
use Phpbe\System\Request;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$config = $this->get('config');

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?app=System&controller=System&task=configMailTestSave', '发送');
$uiEditor->setAction('back', './?app=System&controller=System&task=configMail', '返回');

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'toEmail',
        'label'=>'收件邮箱',
        'value'=>Request::get('toEmail'),
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

$uiEditor->display();
?>
<!--{/center}-->