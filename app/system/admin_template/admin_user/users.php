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
$users = $this->users;
$roles = $this->roles;

$role_map = array('0'=>'所有角色');
foreach ($roles as $role) {
    $role_map[$role->id] = $role->name;
}

$ui_list = be::get_ui('grid');

$ui_list->set_action('list', './?controller=admin_user&task=users');
$ui_list->set_action('create', './?controller=admin_user&task=edit');
$ui_list->set_action('edit', './?controller=admin_user&task=edit');
$ui_list->set_action('unblock', './?controller=admin_user&task=unblock', '启用');
$ui_list->set_action('block', './?controller=admin_user&task=block');
$ui_list->set_action('delete', './?controller=admin_user&task=delete');

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
   ),
    array(
        'type'=>'select',
        'name'=>'role_id',
        'options'=>$role_map,
        'value'=>$this->get('role_id'),
        'width'=>'160px'
   )
);

$config_admin_user = be::get_config('system.admin_user');

foreach ($users as $user) {
    $user->register_time =	date('Y-m-d H:i',$user->register_time);
    $user->last_login_time = date('Y-m-d H:i',$user->last_login_time);
    $user->avatar = '<img src="../'.DATA.'/admin_user/avatar/'.($user->avatar_s == ''?('default/'.$config_admin_user->default_avatar_s):$user->avatar_s).'" width="32" />';
    $user->role_name = '<span class="label label-info">'.$role_map[$user->role_id].'</span>';
}

$ui_list->set_data($users);
$ui_list->set_fields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'order_by'=>'id'
    ),
    array(
        'name'=>'avatar',
        'label'=>'头像',
        'align'=>'center',
        'style'=>'margin:0;padding:2px;',
        'width'=>'50'
    ),
    array(
        'name'=>'username',
        'label'=>'用户名',
        'align'=>'left',
        'order_by'=>'username'
    ),
    array(
        'name'=>'name',
        'label'=>'名称',
        'align'=>'left',
        'width'=>'80'
    ),
    array(
        'name'=>'email',
        'label'=>'邮箱',
        'align'=>'center',
        'width'=>'200',
        'order_by'=>'email'
    ),
    array(
        'name'=>'register_time',
        'label'=>'注册时间',
        'align'=>'center',
        'width'=>'120',
        'order_by'=>'register_time'
    ),
    array(
        'name'=>'last_login_time',
        'label'=>'上次登陆时间',
        'align'=>'center',
        'width'=>'120',
        'order_by'=>'last_login_time'
    ),
    array(
        'name'=>'role_name',
        'label'=>'',
        'align'=>'center',
        'width'=>'80'
    )
);


$ui_list->set_pagination($this->pagination);
$ui_list->order_by($this->order_by, $this->order_by_dir);
$ui_list->display();
?>
<!--{/center}-->
