<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$systemAnnouncements = $this->get('systemAnnouncements');

foreach ($systemAnnouncements as $systemAnnouncement) {
    $systemAnnouncement->createTime =	date('Y-m-d H:i',$systemAnnouncement->createTime);
}

$uiList = Be::getUi('grid');

$uiList->setAction('list', './?controller=systemAnnouncement&action=announcements');
$uiList->setAction('create', './?controller=systemAnnouncement&action=edit');
$uiList->setAction('edit', './?controller=systemAnnouncement&action=edit');
$uiList->setAction('unblock', './?controller=systemAnnouncement&action=unblock');
$uiList->setAction('block', './?controller=systemAnnouncement&action=block');
$uiList->setAction('delete', './?controller=systemAnnouncement&action=delete');


$uiList->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->get('key'),
        'width'=>'120px'
   ),
    array(
        'type'=>'select',
        'name'=>'status',
        'label'=>'状态',
        'options'=>array(
            '-1'=>'所有',
            '0'=>'公开',
            '1'=>'屏蔽'
       ),
        'value'=>$this->get('status'),
        'width'=>'80px'
   )
);

$uiList->setData($systemAnnouncements);

$uiList->setFields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'orderBy'=>'id'
    ),
    array(
        'name'=>'title',
        'label'=>'标题',
        'align'=>'left'
    ),
    array(
        'name'=>'createTime',
        'label'=>'发布时间',
        'align'=>'center',
        'width'=>'120',
        'orderBy'=>'createTime'
    ),
    array(
        'name'=>'ordering',
        'label'=>'排序',
        'align'=>'center',
        'width'=>'40',
        'orderBy'=>'ordering'
    )
);

$uiList->setPagination($this->get('pagination'));
$uiList->orderBy($this->get('orderBy'), $this->get('orderByDir'));
$uiList->display();
?>
<!--{/center}-->