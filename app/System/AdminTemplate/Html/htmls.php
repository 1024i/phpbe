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
$systemHtmls = $this->get('systemHtmls');

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('list', './?controller=systemHtml&action=htmls');
$uiGrid->setAction('create', './?controller=systemHtml&action=edit');
$uiGrid->setAction('edit', './?controller=systemHtml&action=edit');
$uiGrid->setAction('unblock', './?controller=systemHtml&action=unblock');
$uiGrid->setAction('block', './?controller=systemHtml&action=block');
$uiGrid->setAction('delete', './?controller=systemHtml&action=delete');


$uiGrid->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->get('key'),
        'width'=>'120px'
   )
);

$uiGrid->setData($systemHtmls);

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

$uiGrid->setPagination($this->get('pagination'));
$uiGrid->orderBy($this->get('orderBy'), $this->get('orderByDir'));
$uiGrid->display();
?>
<!--{/center}-->