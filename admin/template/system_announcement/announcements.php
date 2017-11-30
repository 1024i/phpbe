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
$system_announcements = $this->get('system_announcements');

foreach ($system_announcements as $system_announcement) {
    $system_announcement->create_time =	date('Y-m-d H:i',$system_announcement->create_time);
}

$ui_list = be::get_admin_ui('grid');

$ui_list->set_action('list', './?controller=system_announcement&task=announcements');
$ui_list->set_action('create', './?controller=system_announcement&task=edit');
$ui_list->set_action('edit', './?controller=system_announcement&task=edit');
$ui_list->set_action('unblock', './?controller=system_announcement&task=unblock');
$ui_list->set_action('block', './?controller=system_announcement&task=block');
$ui_list->set_action('delete', './?controller=system_announcement&task=delete');


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

$ui_list->set_data($system_announcements);

$ui_list->set_fields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'order_by'=>'id'
    ),
    array(
        'name'=>'title',
        'label'=>'标题',
        'align'=>'left'
    ),
    array(
        'name'=>'create_time',
        'label'=>'发布时间',
        'align'=>'center',
        'width'=>'120',
        'order_by'=>'create_time'
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