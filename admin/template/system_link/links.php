<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_list = be::get_admin_ui('grid');
$ui_list->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$system_links = $this->get('system_links');

$ui_list = be::get_admin_ui('grid');

$ui_list->set_action('list', './?controller=system_link&task=links');
$ui_list->set_action('create', './?controller=system_link&task=edit');
$ui_list->set_action('edit', './?controller=system_link&task=edit');
$ui_list->set_action('unblock', './?controller=system_link&task=unblock');
$ui_list->set_action('block', './?controller=system_link&task=block');
$ui_list->set_action('delete', './?controller=system_link&task=delete');

$ui_list->set_filters(
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

$ui_list->set_data($system_links);

$ui_list->set_fields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'order_by'=>'id'
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
        'name'=>'rank',
        'label'=>'权重',
        'align'=>'center',
        'width'=>'40',
        'order_by'=>'rank'
    )
);

$ui_list->set_pagination($this->get('pagination'));
$ui_list->order_by($this->get('order_by'), $this->get('order_by_dir'));
$ui_list->display();
?>
<!--{/center}-->