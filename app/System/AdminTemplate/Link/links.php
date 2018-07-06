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
$systemLinks = $this->get('systemLinks');

$uiList = Be::getUi('grid');

$uiList->setAction('list', './?controller=systemLink&action=links');
$uiList->setAction('create', './?controller=systemLink&action=edit');
$uiList->setAction('edit', './?controller=systemLink&action=edit');
$uiList->setAction('unblock', './?controller=systemLink&action=unblock');
$uiList->setAction('block', './?controller=systemLink&action=block');
$uiList->setAction('delete', './?controller=systemLink&action=delete');

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

$uiList->setData($systemLinks);

$uiList->setFields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'orderBy'=>'id'
    ),
    array(
        'name'=>'name',
        'label'=>'名称',
        'align'=>'left',
        'width'=>'300'
    ),
    array(
        'name'=>'url',
        'label'=>'网址',
        'align'=>'left'
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