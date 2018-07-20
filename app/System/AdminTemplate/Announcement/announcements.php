<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiGrid = Be::getUi('grid');
$uiGrid->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$systemAnnouncements = $this->get('systemAnnouncements');

foreach ($systemAnnouncements as $systemAnnouncement) {
    $systemAnnouncement->createTime =	date('Y-m-d H:i',$systemAnnouncement->createTime);
}

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('list', './?controller=systemAnnouncement&action=announcements');
$uiGrid->setAction('create', './?controller=systemAnnouncement&action=edit');
$uiGrid->setAction('edit', './?controller=systemAnnouncement&action=edit');
$uiGrid->setAction('unblock', './?controller=systemAnnouncement&action=unblock');
$uiGrid->setAction('block', './?controller=systemAnnouncement&action=block');
$uiGrid->setAction('delete', './?controller=systemAnnouncement&action=delete');


$uiGrid->setFilters(
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

$uiGrid->setData($systemAnnouncements);

$uiGrid->setFields(
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

$uiGrid->setPagination($this->get('pagination'));
$uiGrid->orderBy($this->get('orderBy'), $this->get('orderByDir'));
$uiGrid->display();
?>
<!--{/center}-->