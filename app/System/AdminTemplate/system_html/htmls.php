<?php
use System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$systemHtmls = $this->get('systemHtmls');

$uiList = Be::getUi('grid');

$uiList->setAction('list', './?controller=systemHtml&task=htmls');
$uiList->setAction('create', './?controller=systemHtml&task=edit');
$uiList->setAction('edit', './?controller=systemHtml&task=edit');
$uiList->setAction('unblock', './?controller=systemHtml&task=unblock');
$uiList->setAction('block', './?controller=systemHtml&task=block');
$uiList->setAction('delete', './?controller=systemHtml&task=delete');


$uiList->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->get('key'),
        'width'=>'120px'
   )
);

$uiList->setData($systemHtmls);

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
        'align'=>'left'
    ),
    array(
        'name'=>'class',
        'label'=>'调用名',
        'align'=>'center',
        'width'=>'120',
        'orderBy'=>'class'
    )
);

$uiList->setPagination($this->get('pagination'));
$uiList->orderBy($this->get('orderBy'), $this->get('orderByDir'));
$uiList->display();
?>
<!--{/center}-->