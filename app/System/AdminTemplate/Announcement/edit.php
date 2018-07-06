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
$systemAnnouncement = $this->get('systemAnnouncement');

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?controller=systemAnnouncement&action=editSave');	// 显示提交按钮
$uiEditor->setAction('reset');	// 显示重设按钮
$uiEditor->setAction('back');	// 显示返回按钮

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'title',
        'label'=>'标题',
        'value'=>$systemAnnouncement->title,
        'width'=>'75%',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'richtext',
        'name'=>'body',
        'label'=>'内容',
        'value'=>$systemAnnouncement->body,
        'width'=>'600px',
        'height'=>'360px'
   ),
    array(
        'type'=>'text',
        'name'=>'createTime',
        'label'=>'发布时间',
        'value'=>$systemAnnouncement->id == 0?date('Y-m-d H:i:s'):date('Y-m-d H:i:s', $systemAnnouncement->createTime)
   ),
    array(
        'type'=>'text',
        'name'=>'ordering',
        'label'=>'排序',
        'value'=>$systemAnnouncement->ordering,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'radio',
        'name'=>'block',
        'label'=>'状态',
        'value'=>$systemAnnouncement->block,
        'options'=>array('0'=>'公开','1'=>'屏蔽')
    )
);

$uiEditor->addHidden('id', $systemAnnouncement->id);
$uiEditor->display();

?>
<!--{/center}-->