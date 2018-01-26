<?php
use System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$systemHtml = $this->get('systemHtml');

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?controller=systemHtml&task=editSave');	// 显示提交按钮
$uiEditor->setAction('reset');	// 显示重设按钮
$uiEditor->setAction('back');	// 显示返回按钮

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'name',
        'label'=>'名称',
        'value'=>$systemHtml->name,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'class',
        'label'=>'调用名',
        'value'=>$systemHtml->class,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true,
            'remote'=>'./?controller=systemHtml&task=checkClass&id='.$systemHtml->id
       ),
        'message'=>array(
            'remote'=>'调用名已被占用！'
       )
   ),
    array(
        'type'=>'richtext',
        'name'=>'body',
        'label'=>'内容',
        'value'=>$systemHtml->body,
        'width'=>'600px',
        'height'=>'360px'
   ),
    array(
        'type'=>'radio',
        'name'=>'block',
        'label'=>'状态',
        'value'=>$systemHtml->block,
        'options'=>array('0'=>'公开','1'=>'屏蔽')
    )
);

$uiEditor->addHidden('id', $systemHtml->id);
$uiEditor->display();
?>
<!--{/center}-->