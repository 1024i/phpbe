<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$systemLink = $this->get('systemLink');

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?controller=systemLink&action=editSave');	// 显示提交按钮
$uiEditor->setAction('reset');	// 显示重设按钮
$uiEditor->setAction('back');	// 显示返回按钮

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'name',
        'label'=>'名称',
        'value'=>$systemLink->name,
        'width'=>'300px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'url',
        'label'=>'网址',
        'value'=>$systemLink->url,
        'width'=>'500px',
        'validate'=>array(
            'required'=>true,
            'url'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'ordering',
        'label'=>'排序',
        'value'=>$systemLink->ordering,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'radio',
        'name'=>'block',
        'label'=>'状态',
        'value'=>$systemLink->block,
        'options'=>array('0'=>'公开','1'=>'屏蔽')
    )
);

$uiEditor->addHidden('id', $systemLink->id);
$uiEditor->display();
?>
<!--{/center}-->