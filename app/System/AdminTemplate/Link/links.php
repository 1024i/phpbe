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
$systemLinks = $this->get('systemLinks');

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('list', './?controller=systemLink&action=links');
$uiGrid->setAction('create', './?controller=systemLink&action=edit');
$uiGrid->setAction('edit', './?controller=systemLink&action=edit');
$uiGrid->setAction('unblock', './?controller=systemLink&action=unblock');
$uiGrid->setAction('block', './?controller=systemLink&action=block');
$uiGrid->setAction('delete', './?controller=systemLink&action=delete');

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

$uiGrid->setData($systemLinks);

$uiGrid->setFields(
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

$uiGrid->setPagination($this->get('pagination'));
$uiGrid->orderBy($this->get('orderBy'), $this->get('orderByDir'));
$uiGrid->display();
?>
<!--{/center}-->