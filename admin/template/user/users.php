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
$users = $this->users;
$roles = $this->roles;

$role_map = array('0'=>'所有用户组');
foreach ($roles as $role) {
    // 游客角色
    if ($role->id == 1) continue;
    $role_map[$role->id] = $role->name;
}

$ui_list = be::get_admin_ui('grid');

$ui_list->set_action('list', './?controller=user&task=users');
$ui_list->set_action('create', './?controller=user&task=edit');
$ui_list->set_action('edit', './?controller=user&task=edit');
$ui_list->set_action('unblock', './?controller=user&task=unblock', '启用');
$ui_list->set_action('block', './?controller=user&task=block');
$ui_list->set_action('delete', './?controller=user&task=delete');

$ui_list->set_filters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->key,
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
        'value'=>$this->status,
        'width'=>'80px'
   ),
    array(
        'type'=>'select',
        'name'=>'role_id',
        'options'=>$role_map,
        'value'=>$this->group_id,
        'width'=>'160px'
   )
);

$config_user = be::get_config('user');

$admin_config_user_group->names[0] = '';
foreach ($users as $user) {
    $user->register_time =	date('Y-m-d H:i',$user->register_time);
    $user->last_login_time = $user->last_login_time == 0?'-':date('Y-m-d H:i',$user->last_login_time);
    $user->avatar = '<img src="../'.DATA.'/user/avatar/'.($user->avatar_s == ''?('default/'.$config_user->default_avatar_s):$user->avatar_s).'" width="32" />';

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