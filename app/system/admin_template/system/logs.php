<?php
use system\be;
?>

<!--{head}-->
<?php
    $ui_list = be::get_ui('grid');
    $ui_list->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$logs = $this->get('logs');

$ui_list = be::get_ui('grid');

$ui_list->set_action('listing', './?controller=system&task=logs');

$options = array();
$options['0'] = '所有';
foreach ($this->admin_users as $admin_user) {
    $options[$admin_user->id] = $admin_user->username;
}

$ui_list->set_filters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'搜索',
        'value'=>$this->get('key'),
        'width'=>'100px'
   ),
    array(
        'type'=>'select',
        'name'=>'user_id',
        'label'=>'指定管理员',
        'options'=>$options,
        'value'=>$this->get('user_id')
   ),
    array(
        'type'=>'button',
        'value'=>'删除三个月前日志',
        'click'=>'javascript:deleteLogs(this);',
        'class'=>'btn btn-danger'
   )
);

$lib_ip = be::get_lib('ip');

$date = '';
foreach ($logs as $log) {
    $new_date = date('Y-m-d',$log->create_time);
    if ($date == $new_date) {
        $log->create_time = '<span style="visibility:hidden;">'. $new_date .' &nbsp;</span>'. date('H:i:s',$log->create_time);
    } else {
        $log->create_time = $new_date .' &nbsp;'. date('H:i:s',$log->create_time);
        $date = $new_date;
    }
    $log->username = be::get_user($log->user_id)->username;
    $log->address = $lib_ip->convert($log->ip);
}

$ui_list->set_data($logs);

$ui_list->set_fields(
    array(
        'name'=>'create_time',
        'label'=>'时间',
        'align'=>'center',
        'width'=>'150'
    ),
    array(
        'name'=>'username',
        'label'=>'用户名',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'title',
        'label'=>'操作',
        'align'=>'left'
    ),
    array(
        'name'=>'ip',
        'label'=>'IP',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'address',
        'label'=>'地理位置',
        'align'=>'left',
        'width'=>'200'
    )
);

$ui_list->set_pagination($this->get('pagination'));
$ui_list->display();
?>
<!--{/center}-->