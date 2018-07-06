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
$systemHtmls = $this->get('systemHtmls');

$uiList = Be::getUi('grid');

$uiList->setAction('list', './?controller=systemHtml&action=htmls');
$uiList->setAction('create', './?controller=systemHtml&action=edit');
$uiList->setAction('edit', './?controller=systemHtml&action=edit');
$uiList->setAction('unblock', './?controller=systemHtml&action=unblock');
$uiList->setAction('block', './?controller=systemHtml&action=block');
$uiList->setAction('delete', './?controller=systemHtml&action=delete');


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